<?php
namespace AppBundle\Topic;

use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\Client\Auth\WebsocketAuthenticationProviderInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Ratchet\Wamp\WampConnection;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use AppBundle\Entity\ChatLog;
use AppBundle\Entity\Room;
use AppBundle\Entity\Video;
use Psr\Log\LoggerInterface;

abstract class AbstractTopic implements TopicInterface
{
  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var ClientManipulatorInterface
   */
  protected $clientManipulator;

  /**
   * @var JWTTokenAuthenticator
   */
  protected $tokenAuthenticator;

  /**
   * @var UserProviderInterface
   */
  protected $userProvider;

  /**
   * @var WebsocketAuthenticationProviderInterface
   */
  protected $authenticationProvider;

  /**
   * @var EntityManagerInterface
   */
  protected $em;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @param ContainerInterface $container
   * @param LoggerInterface $logger
   */
  public function __construct(ContainerInterface $container, LoggerInterface $logger)
  {
    $this->container              = $container;
    $this->clientManipulator      = $container->get("gos_web_socket.websocket.client_manipulator");
    $this->tokenAuthenticator     = $container->get("lexik_jwt_authentication.security.guard.jwt_token_authenticator");
    $this->userProvider           = $container->get("fos_user.user_provider.username");
    $this->authenticationProvider = $container->get("gos_web_socket.websocket_authentification.provider");
    $this->em                     = $container->get("doctrine.orm.default_entity_manager");
    $this->logger                 = $logger;
  }

  /**
   * This will receive any Subscription requests for this topic.
   *
   * @param ConnectionInterface|WampConnection $connection
   * @param Topic $topic
   * @param WampRequest $request
   * @return void
   */
  public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
  {
/*    $topic->broadcast([
      'cmd' => Commands::JOIN,
      'msg' => $connection->resourceId . " has joined " . $topic->getId()
    ]);*/
  }

  /**
   * This will receive any UnSubscription requests for this topic.
   *
   * @param ConnectionInterface|WampConnection $connection
   * @param Topic $topic
   * @param WampRequest $request
   * @return void
   */
  public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
  {
/*    $topic->broadcast([
      'cmd' => Commands::LEAVE,
      'msg' => $connection->resourceId . " has left " . $topic->getId()
    ]);*/
  }

  /**
   * Authenticates the user
   *
   * @param ConnectionInterface $connection
   * @param string $token
   * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
   */
  protected function authenticate(ConnectionInterface $connection, $token)
  {
    $connection->WebSocket->request->getQuery()->set("token", $token);
    return $this->authenticationProvider->authenticate($connection);
  }

  /**
   * @param string $key
   * @return mixed
   */
  protected function getParameter($key)
  {
    return $this->container->getParameter($key);
  }

  /**
   * @param ConnectionInterface $connection
   * @param array $event
   * @return UserInterface
   */
  protected function getUser(ConnectionInterface $connection, array $event = [])
  {
    if (empty($event["token"])) {
      $user = $this->clientManipulator->getClient($connection);
    } else {
      $request = new Request();
      $request->headers->set('Authorization', 'Bearer ' . $event["token"]);
      $creds = $this->tokenAuthenticator->getCredentials($request);
      if (!$creds) {
        $user = $this->clientManipulator->getClient($connection);
      } else {
        $user = $this->tokenAuthenticator->getUser($creds, $this->userProvider);
      }
    }

    if ($user instanceof UserInterface) {
      $user = $this->em->getRepository("AppBundle:User")->findByUsername($user->getUsername());
    }

    return $user;
  }

  /**
   * @param string $roomName
   * @param UserInterface $user
   * @return Room
   */
  protected function getRoom($roomName, UserInterface $user = null)
  {
    $repo = $this->em->getRepository("AppBundle:Room");
    $room = $repo->findByName($roomName);
    if (!$room && $user !== null) {
      $room = new Room($roomName, $user);
      $this->em->merge($room);
      $this->em->flush();
    }

    return $room;
  }

  /**
   * @param UserInterface $user
   * @return array
   */
  protected function serializeUser(UserInterface $user)
  {
    $username = $user->getUsername();
    return [
      "username" => $username,
      "avatar"   => "https://robohash.org/${username}?set=set3",
      "profile"  => "https://upnext.fm/u/${username}",
      "roles"    => $user->getRoles()
    ];
  }

  /**
   * @param Video $video
   * @return array
   */
  protected function serializeVideo(Video $video)
  {
    return [
      "codename"  => $video->getCodename(),
      "provider"  => $video->getProvider(),
      "permalink" => $video->getPermalink(),
      "thumbnail" => $video->getThumbSmall(),
      "title"     => $video->getTitle(),
      "seconds"   => $video->getSeconds()
    ];
  }

  /**
   * @param ChatLog[] $messages
   * @return array
   */
  protected function serializeMessages($messages)
  {
    $serialized = [];
    foreach($messages as $message) {
      $serialized[] = [
        "id"      => $message->getId(),
        "date"    => $message->getDateCreated()->format("D M d Y H:i:s O"),
        "from"    => $message->getUser()->getUsername(),
        "message" => $message->getMessage()
      ];
    }

    return $serialized;
  }
}
