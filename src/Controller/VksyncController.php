<?php

/**
 * @file
 * Contains \Drupal\vksync\Controller\VksyncController.
 */

namespace Drupal\vksync\Controller;

/**
 * Controller routines for vksync routes.
 */
class VksyncController {
  /**
   * Get params to connect VK API methods
   * @return array
   *   Parameters
   */
  private function params($params = array()) {
    if ($params) {
      $pice = array();
      foreach($params as $key => $param) {
        $pice[] = $key . '=' . urlencode($param);
      }
      return implode('&', $pice);
    }
    else {
      $config = \Drupal::config('vksync.settings');
      $params = array(
        'owner_id' => '-' . $config->get('vksync_owner_id'),
        'v' => VKSYNC_API_VERSION,
        'access_key' => $config->get('vksync_app_secret'),
        'access_token' => $config->get('vksync_access_token'),
      );
      return $params;
    }
  }

  /**
   * Function makes http query to VK.
   */
  public function get($method, $params, $request_url = VKSYNC_API_REQUEST_URI) {
    $url = $request_url . '/' . $method . '?' . $this->params($params);
    $content = file_get_contents($url);

    // Return request result.
    return drupal_json_decode($content);
  }

  /**
   * Returns a list of a user's or community's photo albums.
   * This is an open method; it does not require an access_token.
   *
   * @param  array $album_ids
   *   Album IDs.
   * @return array
   *   Returns an array of album objects.
   */
  public function getAlbums($album_ids = array()) {
    $params = $this->params();
    $params['need_covers'] = 1;
    $params['photo_sizes'] = 1;
    if ($album_ids) {
      $params['album_ids'] = implode(',', $album_ids);
    }
    $result = $this->get('photos.getAlbums', $params);
    return $result;
  }

  public function getById($photos = array()) {
    $params = $this->params();
    $params['extended'] = 1;
    $params['photo_sizes'] = 1;
    if ($photos) {
      foreach ($photos as $key => $id) {
        $photos[$key] = $params['owner_id'] . '_' . $id . '_' . $params['access_key'];
      }
    }
    $params['photos'] = implode(',', $photos);
    $result = $this->get('photos.getById', $params);
    return $result;
  }

  public function test() {
    $albums = $this->getAlbums();
    if ($albums['response']['items']) {
      foreach ($albums['response']['items'] as $key => $item) {
        $sizes = $item['sizes'];
        foreach ($sizes as $k => $size) {
          if (in_array('w', $size)) {
            $albums['response']['items'][$key]['sizes'] = $size;
          }
          elseif(in_array('z', $size)) {
            $albums['response']['items'][$key]['sizes'] = $size;
          }
          elseif(in_array('y', $size)) {
            $albums['response']['items'][$key]['sizes'] = $size;
          }
        }
      }
    }
    // print "<pre>";
    // print_r($albums);
    // print "</pre>";
    // die();
  }

  /**
   * Log messages and print it on the screen.
   *
   * @param $message
   *   Array with message and it severity.
   * @param $link
   *   Link to view node.
   */
  public function _vksync_watchdog($message, $link = NULL) {
    // Set message about event.
    drupal_set_message($message['text'], $message['severity']);

    // Log event into watchdog.
    if ($message['severity'] == 'status') {
      $severity = WATCHDOG_INFO;
    }
    elseif ($message['severity'] == 'warning') {
      $severity = WATCHDOG_WARNING;
    }
    else {
      $severity = WATCHDOG_ERROR;
    }

    watchdog('vksync', $message['text'], array(), $severity, $link);
  }
}
