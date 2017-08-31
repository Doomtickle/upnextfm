<?php
namespace AppBundle\Service;

use AppBundle\Playlist\Providers;
use ColorThief\ColorThief;
use Madcoda\Youtube\Youtube;
use Psr\Log\LoggerInterface;

class VideoService
{
  /**
   * @var Youtube
   */
  protected $youtube;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * Constructor
   *
   * @param LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  /**
   * @param Youtube $youtube
   * @return $this
   */
  public function setYoutube(Youtube $youtube)
  {
    $this->youtube = $youtube;
    return $this;
  }

  /**
   * @param string $codename
   * @param string $provider
   * @return VideoInfo
   */
  public function getInfo($codename, $provider)
  {
    $this->logger->debug(sprintf(
      "Fetching video info for '%s'@'%s'.",
      $codename,
      $provider
    ));

    switch($provider) {
      case Providers::YOUTUBE:
        if ($resp = $this->youtube->getVideoInfo($codename)) {
          return $this->youtubeInfo($codename, $resp);
        }
        break;
    }

    return null;
  }

  /**
   * @param string $codename
   * @param string $provider
   * @return array
   */
  public function getPlaylist($codename, $provider)
  {
    $this->logger->debug(sprintf(
      "Fetching playlist info for '%s'@'%s'.",
      $codename,
      $provider
    ));

    switch($provider) {
      case Providers::YOUTUBE:
        $resp = $this->youtube->getPlaylistItemsByPlaylistId($codename, 25);
        if ($resp) {
          $codenames = [];
          foreach($resp as $r) {
            $codenames[] = $r->contentDetails->videoId;
          }

          return $codenames;
        }
        break;
    }

    return [];
  }

  /**
   * @param string $codename
   * @param object $resp
   * @return VideoInfo
   */
  protected function youtubeInfo($codename, $resp)
  {
    $info = new VideoInfo($codename, Providers::YOUTUBE, "https://youtu.be/${codename}");
    $info
      ->setTitle($resp->snippet->title)
      ->setSeconds($this->youtubeToSeconds($resp->contentDetails->duration))
      ->setDescription($resp->snippet->description)
      ->setThumbnail("sm", !empty($resp->snippet->thumbnails->standard->url)
        ? $resp->snippet->thumbnails->standard->url
        : $resp->snippet->thumbnails->default->url)
      ->setThumbnail("md", !empty($resp->snippet->thumbnails->medium->url)
        ? $resp->snippet->thumbnails->medium->url
        : $resp->snippet->thumbnails->default->url)
      ->setThumbnail("lg", !empty($resp->snippet->thumbnails->high->url)
        ? $resp->snippet->thumbnails->high->url
        : $resp->snippet->thumbnails->default->url);
    $info->setThumbColor($this->getThumbColor($info->getThumbnail("sm")));

    return $info;
  }

  /**
   * @param string $duration
   * @return int
   */
  protected function youtubeToSeconds($duration)
  {
    $start = new \DateTime('@0'); // Unix epoch
    $start->add(new \DateInterval($duration));
    return $start->getTimestamp();
  }

  /**
   * @param string $thumbURL
   * @return string
   */
  protected function getThumbColor($thumbURL)
  {
    $thumbColor = "000000";
    try {
      $dominantColor = ColorThief::getColor($thumbURL, 3);
      $thumbColor    = sprintf("%02x%02x%02x", $dominantColor[0], $dominantColor[1], $dominantColor[2]);
    } catch (\Exception $e) {}

    return $this->adjustLuminance($thumbColor, 0.3);
  }

  /**
   * @param string $hex
   * @param float $percent
   * @return string
   */
  protected function adjustLuminance($hex, $percent)
  {
    $newHex = '';
    if (strlen($hex) < 6) {
      $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
    }
    for ($i = 0; $i < 3; $i++) {
      $dec = hexdec(substr( $hex, $i*2, 2 ));
      $dec = min(max(0, $dec + $dec * $percent), 255);
      $newHex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
    }

    return $newHex;
  }
}
