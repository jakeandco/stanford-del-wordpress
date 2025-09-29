<?php

namespace JakeAndCo\Media;


class Video
{

  /**
   * Grab the url of a publicly embeddable video hosted on Vimeo
   * @param  string $video_url The "embed" url of a video
   * @return string            The url of the thumbnail, or false if there's an error
   */
  public static function video_thumbnail_grab($video_url)
  {
    if (!$video_url) return false;

    // Create a unique transient key based on the Vimeo URL
    $transient_key = 'vimeo_thumb_' . md5($video_url);

    // Try to get the thumbnail URL from the transient
    $cached_thumbnail = get_transient($transient_key);

    if ($cached_thumbnail !== false) {
      // If a cached version is found, return it
      return $cached_thumbnail;
    } else {
      // If not cached, fetch the thumbnail URL from Vimeo
      $thumbnail_uri = self::video_thumbnail_get_uri($video_url);

      if (!$thumbnail_uri) return false;

      // Store the thumbnail URL in a transient for 120 days.
      set_transient($transient_key, $thumbnail_uri, 120 * DAY_IN_SECONDS);

      return $thumbnail_uri;
    }
  }

  /* Pull apart OEmbed video link to get thumbnails out*/
  public static function video_thumbnail_get_uri($video_uri)
  {

    $thumbnail_uri = '';

    // determine the type of video and the video id
    $video = self::video_parse_uri($video_uri);

    // get youtube thumbnail
    if ($video['type'] == 'youtube')
      $thumbnail_uri = self::video_thumbnail_get_uri_youtube($video['id']);
    // get vimeo thumbnail
    if ($video['type'] == 'vimeo')
      $thumbnail_uri = self::video_thumbnail_get_uri_vimeo($video['id']);
    // get default/placeholder thumbnail
    if (empty($thumbnail_uri) || is_wp_error($thumbnail_uri))
      $thumbnail_uri = '';

    //return thumbnail uri
    return $thumbnail_uri;
  }


  /* Parse the video uri/url to determine the video type/source and the video id */
  public static function video_parse_uri($url)
  {

    // Parse the url
    $parse = parse_url($url);

    // Set blank variables
    $video_type = '';
    $video_id = '';

    // Url is http://youtu.be/xxxx
    // Url is http://www.youtube.com/watch?v=xxxx
    // or http://www.youtube.com/watch?feature=player_embedded&v=xxx
    // or http://www.youtube.com/embed/xxxx
    if (($parse['host'] == 'youtu.be') || ($parse['host'] == 'youtube.com') || ($parse['host'] == 'www.youtube.com')) {
      $video_type = 'youtube';
      $video_id = self::video_get_id_youtube($url);
    }

    // Url is http://www.vimeo.com
    if (($parse['host'] == 'vimeo.com') || ($parse['host'] == 'www.vimeo.com')) {
      $video_type = 'vimeo';
      $video_id = self::video_get_id_vimeo($url);
    }

    // If recognised type return video array
    if (!empty($video_type)) {

      $video_array = array(
        'type' => $video_type,
        'id' => $video_id
      );

      return $video_array;
    } else {

      return false;
    }
  }


  /* Takes a Youtube video/clip ID and returns default thumbnail */
  public static function video_thumbnail_get_uri_youtube($clip_id)
  {

    return 'http://img.youtube.com/vi/' . $clip_id . '/hqdefault.jpg';
  }

  /* Takes a Vimeo video/clip ID and calls the Vimeo API v2 to get the large thumbnail URL.*/
  public static function video_thumbnail_get_uri_vimeo($clip_id)
  {

    $vimeo_api_uri = 'http://vimeo.com/api/v2/video/' . $clip_id . '.php';
    $vimeo_response = wp_remote_get($vimeo_api_uri);
    if (is_wp_error($vimeo_response)) {
      return $vimeo_response;
    } else {
      $vimeo_response = unserialize($vimeo_response['body']);
      return $vimeo_response[0]['thumbnail_large'];
    }
  }

  /* Takes a Vimeo video/clip ID and calls the Vimeo API v2 to get the large thumbnail URL.*/

  public static function video_get_id_youtube($url)
  {
    // Here is a sample of the URLs this regex matches: (there can be more content after the given URL that will be ignored)

    // http://youtu.be/dQw4w9WgXcQ
    // http://www.youtube.com/embed/dQw4w9WgXcQ
    // http://www.youtube.com/watch?v=dQw4w9WgXcQ
    // http://www.youtube.com/?v=dQw4w9WgXcQ
    // http://www.youtube.com/v/dQw4w9WgXcQ
    // http://www.youtube.com/e/dQw4w9WgXcQ
    // http://www.youtube.com/user/username#p/u/11/dQw4w9WgXcQ
    // http://www.youtube.com/sandalsResorts#p/c/54B8C800269D7C1B/0/dQw4w9WgXcQ
    // http://www.youtube.com/watch?feature=player_embedded&v=dQw4w9WgXcQ
    // http://www.youtube.com/?feature=player_embedded&v=dQw4w9WgXcQ

    // It also works on the youtube-nocookie.com URL with the same above options.
    // It will also pull the ID from the URL in an embed code (both iframe and object tags)

    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return $match[1];
  }

  public static function video_get_id_vimeo($url)
  {
    $parse = parse_url($url);
    return ltrim($parse['path'], '/');
  }
}
