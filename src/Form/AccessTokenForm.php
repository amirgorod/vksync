<?php

/**
 * @file AccessTokenForm.php
 * Contains \Drupal\vksync\Form\AccessTokenForm.
 */

namespace Drupal\vksync\Form;

use Drupal\vksync\Controller\VksyncController as Vksync;
use Drupal\Core\Form\FormInterface;

/**
 *
 */
class AccessTokenForm implements FormInterface {
  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'vksync_access_token_form';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
    $config = \Drupal::config('vksync.settings');
    $html_id = drupal_html_id($this->getFormID());
    $form['#prefix'] = '<div id="' . $html_id . '">';
    $form['#suffix'] = '</div>';

    $form['new_access_token'] = array(
      '#type' => 'fieldset',
      '#title' => t('Recieve new access token'),
      '#collapsible' => TRUE,
      '#collapsed' => $config->get('vksync_access_token') ? TRUE : FALSE,
    );

    $form['new_access_token']['link'] = array(
      '#type' => 'link',
      '#title' => t('Get code'),
      '#href' => VKSYNC_AUTHORIZE_URI,
      '#options' => array(
        'query' => array(
          'client_id' => $config->get('vksync_app_id'),
          'scope' => VKSYNC_AUTHORIZE_SCOPE,
          'redirect_uri' => VKSYNC_REDIRECT_URI,
          'response_type' => VKSYNC_AUTHORIZE_RESPONSE_TYPE,
        ),
        'html' => FALSE,
        'attributes' => array(
          'target' => '_blank',
        ),
      ),
    );

    $form['new_access_token']['code'] = array(
      '#type' => 'textfield',
      '#title' => t('Code'),
      '#description' => t('Copy #code param from the URL here.'),
    );

    $form['new_access_token']['actions'] = array(
      '#type' => 'action',
    );

    $form['new_access_token']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Get access token'),
    );
    return $form;
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, array &$form_state) {

  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    // Collect params for authorization.
    // See http://vk.com/developers.php?oid=-1&p=%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F_%D1%81%D0%B0%D0%B9%D1%82%D0%BE%D0%B2
    $config = \Drupal::config('vksync.settings');
    $params = array(
      'client_id' => $config->get('vksync_app_id'),
      'client_secret' => $config->get('vksync_app_secret'),
      'code' => $form_state['values']['code'],
      'redirect_uri' => VKSYNC_REDIRECT_URI,
    );

    $data = Vksync::_vksync_query('', $params, VKSYNC_ACCESS_TOKEN_URI);

    // Access token was recieved.
    if (!empty($data['access_token'])) {
      $message = array(
        'text' => t('Access token was successfully recieved.'),
        'severity' => 'status',
      );
      Vksync::_vksync_watchdog($message);

      $config->set('vksync_access_token', $data['access_token'])->save();

      // Redirect user to the settings page.
      $form_state['redirect'] = 'admin/config/services/vksync';
    }
    // Access token was not recieved.
    elseif (isset($data['error']) && isset($data['error_description'])) {
      $message = array(
        'text' => t('Access token was not recieved. Reason: %error',
                    array('%error' => check_plain($data['error_description']))),
        'severity' => 'error',
      );
      Vksync::_vksync_watchdog($message);

      // Remove variable if user not allowed to get a new access token.
      // variable_del('vksync_access_token');
      $config->set('vksync_access_token', '')->save();
    }

    return $form;
  }

}
