<?php
namespace AppBundle\Topic;

use AppBundle\Playlist\ProvidersInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\TopicPeriodicTimerTrait;
use Gos\Bundle\WebSocketBundle\Topic\TopicPeriodicTimerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use AppBundle\Entity\Room;
use AppBundle\Entity\User;
use AppBundle\Entity\Video;
use AppBundle\Entity\VideoLog;
use Predis\Client as Redis;

class VideoTopic extends AbstractTopic implements TopicPeriodicTimerInterface
{
  use TopicPeriodicTimerTrait;

  /**
   * @var array
   */
  protected $subs = [];

  /**
   * @var Redis
   */
  protected $redis;

  /**
   * @var ProvidersInterface
   */
  protected $providers;

  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return "video.topic";
  }

  /**
   * @param ProvidersInterface $providers
   * @return $this
   */
  public function setProviders(ProvidersInterface $providers)
  {
    $this->providers = $providers;
    return $this;
  }

  /**
   * @param Redis $redis
   * @return $this
   */
  public function setRedis(Redis $redis)
  {
    $this->redis = $redis;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function onSubscribe(ConnectionInterface $conn, Topic $topic, WampRequest $request)
  {
    $user = $this->getUser($conn);
    if (!($user instanceof UserInterface)) {
      $user = null;
    }
    $room = $this->getRoom($request->getAttributes()->get("room"), $user);
    if (!$room) {
      $this->logger->error("Room not found or created.");
      return;
    }

    $client   = ["conn" => $conn, "id" => $topic->getId()];
    $roomName = $room->getName();
    if (!isset($this->subs[$roomName])) {
      $this->subs[$roomName] = [];
    }
    if (!empty($this->subs[$roomName])) {
      $index = array_search($client, $this->subs[$roomName]);
      if (false !== $index) {
        unset($this->subs[$roomName][$index]);
      }
    }
    $this->subs[$roomName][] = $client;

    $videoID = $this->redis->get(sprintf("room:%s:playing", $roomName));
    if ($videoID) {
      $video = $this->em->getRepository("AppBundle:Video")->findByID($videoID);
      if ($video) {
        $conn->event($topic->getId(), [
          "cmd"   => VideoCommands::START,
          "video" => $this->serializeVideo($video)
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
  {
    $user = $this->getUser($connection);
    if (!($user instanceof UserInterface)) {
      $user = null;
    }
    $room = $this->getRoom($request->getAttributes()->get("room"), $user);
    if (!$room) {
      $this->logger->error("Room not found or created.");
      return;
    }

    $roomName = $room->getName();
    if (!isset($this->subs[$roomName])) {
      $this->subs[$roomName] = [];
    }
    if (!empty($this->subs[$roomName])) {
      $client = ["conn" => $connection, "id" => $topic->getId()];
      $index  = array_search($client, $this->subs[$roomName]);
      if (false !== $index) {
        unset($this->subs[$roomName][$index]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onPublish(
    ConnectionInterface $conn,
    Topic $topic,
    WampRequest $req,
    $event,
    array $exclude,
    array $eligible)
  {
    try {
      $event = array_map("trim", $event);
      if (empty($event["cmd"])) {
        $this->logger->error("cmd not set.", $event);
        return;
      }
      $user = $this->getUser($conn);
      if (!($user instanceof UserInterface)) {
        $this->logger->error("User not found.", $event);
        return;
      }
      $room = $this->getRoom($req->getAttributes()->get("room"), $user);
      if (!$room || $room->isDeleted()) {
        $this->logger->error("Room not found.", $event);
        return;
      }

      switch ($event["cmd"]) {
        case VideoCommands::PLAY:
          $this->handlePlay($conn, $topic, $req, $room, $user, $event);
          break;
      }
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * @param ConnectionInterface $conn
   * @param Topic $topic
   * @param WampRequest $req
   * @param Room $room
   * @param UserInterface|User $user
   * @param array $event
   * @return mixed|void
   */
  protected function handlePlay(
    ConnectionInterface $conn,
    Topic $topic,
    WampRequest $req,
    Room $room,
    UserInterface $user,
    array $event)
  {
    $parsed = $this->providers->parseURL($event["url"]);
    if (!$parsed) {
      return $this->connSendError($conn, $topic,
        "Invalid URL \"${event['url']}\"."
      );
    }

/*    $msg = [
      "provider"  => $parsed["provider"],
      "codename"  => $parsed["codename"],
      "user_id"   => $user->getId(),
      "room_id"   => $room->getId(),
      "video_log" => true
    ];
    $this->container->get('old_sound_rabbit_mq.save_video_producer')->publish(json_encode($msg));*/

    $video = $this->em->getRepository("AppBundle:Video")
      ->findByCodename($parsed["codename"], $parsed["provider"]);
    if (!$video) {
      $service = $this->container->get("app.service.video");
      $info    = $service->getInfo($parsed["codename"], $parsed["provider"]);
      if (!$info) {
        $this->logger->error("Failed to fetch video info.", $event);
        return true;
      }

      $video = new Video();
      $video->setCodename($parsed["codename"]);
      $video->setProvider($parsed["provider"]);
      $video->setCreatedByUser($user);
      $video->setCreatedInRoom($room);
      $video->setTitle($info->getTitle());
      $video->setSeconds($info->getSeconds());
      $video->setPermalink($info->getPermalink());
      $video->setThumbColor($info->getThumbColor());
      $video->setThumbSm($info->getThumbnail("sm"));
      $video->setThumbMd($info->getThumbnail("md"));
      $video->setThumbLg($info->getThumbnail("lg"));
      $video->setNumPlays(0);
    }

    $video->setDateLastPlayed(new \DateTime());
    $video->incrNumPlays();
    $this->em->persist($video);

    $this->playing[$room->getName()] = $video->getId();
    $this->redis->set(sprintf("room:%s:playing", $room->getName()), $video->getId());

    $videoLog = new VideoLog($video, $room, $user);
    $this->em->merge($videoLog);
    $this->em->flush();

    $topic->broadcast([
      "cmd"   => VideoCommands::START,
      "video" => $this->serializeVideo($video)
    ]);
  }

  /**
   * @param Topic $topic
   *
   * @return mixed
   */
  public function registerPeriodicTimer(Topic $topic)
  {
    $interval = $this->container->getParameter("app_ws_video_time_update_interval");
    $this->periodicTimer->addPeriodicTimer($this, VideoCommands::TIME_UPDATE, $interval, function() use ($topic) {

      $play = $this->redis->get("playlist:play");
      if ($play) {
        $this->redis->del("playlist:play");
        $play = json_decode($play, true);
        $roomName = $play["roomName"];

        if (isset($this->subs[$roomName])) {
          $video = $this->em->getRepository("AppBundle:Video")->findByID($play["videoID"]);
          if ($video) {
            foreach($this->subs[$roomName] as $client) {
              $client["conn"]->event($client["id"], [
                "cmd"   => VideoCommands::START,
                "video" => $this->serializeVideo($video)
              ]);
            }
          }
        }
      }
    });
  }
}
