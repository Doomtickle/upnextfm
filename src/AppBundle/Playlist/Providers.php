<?php
namespace AppBundle\Playlist;

class Providers implements ProvidersInterface
{
  const YOUTUBE = "youtube";

  /**
   * @param string $provider
   * @return bool
   */
  public static function isValidProvider($provider)
  {
    return in_array($provider, [self::YOUTUBE]);
  }

  /**
   * @param $mediaURL
   * @return array|null
   */
  public function parseURL($mediaURL)
  {
    $mediaURL = trim($mediaURL);

    if (preg_match('/youtube\.com\/watch\?([^#]+)/i', $mediaURL, $matches)) {
      parse_str($matches[1], $query);
      if (!empty($query["v"])) {
        return [
          "codename" => $query["v"],
          "provider" => self::YOUTUBE
        ];
      }
    }

    if (preg_match('/youtu\.be\/([^\?&#]+)/i', $mediaURL, $matches)) {
      return [
        "codename" => $matches[1],
        "provider" => self::YOUTUBE
      ];
    }

    return null;
  }
}
