<?php

/**
 * @file SettingsForm.php
 * Contains \Drupal\vksync\Form\SettingsForm.
 */

namespace Drupal\vksync\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 *
 */
class SettingsForm extends ConfigFormBase {
  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'vksync_settings_form';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
    $config = \Drupal::config('vksync.settings');
    $form['access_token'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Access token'),
      '#collapsible' => TRUE,
    );

    $form['access_token']['status'] = array(
      '#type' => 'markup',
      '#markup' => $config->get('vksync_access_token') ?
        '<span style="color:green;">' . $this->t('Recieved') . '</span>' :
        '<span style="color:red;">' . $this->t('Not recieved') . '</span>',
    );

    // Application settings.
    $form['application'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Application settings'),
      '#collapsible' => TRUE,
      '#description' => $this->t('Note: You have to <a href="@url" target="_blank">create</a> a <strong>standalone</strong> application to make this module work.',
        array('@url' => VKSYNC_EDITAPP_URI)),
    );

    $form['application']['app_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Application ID'),
      '#description' => $this->t('ID of vk application.'),
      '#default_value' => $config->get('vksync_app_id'),
      '#required' => TRUE,
      '#element_validate' => array('element_validate_integer_positive'),
    );

    $form['application']['app_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Application secret code'),
      '#description' => $this->t('Secret code of vk application.'),
      '#default_value' => $config->get('vksync_app_secret'),
      '#required' => TRUE,
    );

    // Owner settings.
    $form['owner'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Owner settings'),
      '#description' => $this->t('Set user or group to which data will be transferred.'),
      '#collapsible' => TRUE,
    );

    $form['owner']['owner_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Owner ID'),
      '#description' => $this->t('Group ID.'),
      // '#description' => $this->t('User ID or Group ID.'),
      '#default_value' => $config->get('vksync_owner_id'),
      '#required' => TRUE,
      // '#element_validate' => array('element_validate_integer_positive'),
    );

    $form['owner']['wall_owner'] = array(
      '#type' => 'select',
      '#title' => $this->t('Select owner type'),
      '#description' => $this->t('Who owns the ID above.'),
      '#required' => TRUE,
      '#options' => array(
        '' => $this->t('- None -'),
        // 'user' => $this->t('User'),
        'group' => $this->t('Group'),
      ),
      '#default_value' => $config->get('vksync_wall_owner'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, array &$form_state) {
    $values = &$form_state['values'];

    // Remove spaces. Just in case of small user mistake.
    $values['app_id'] = trim($values['app_id']);
    $values['app_secret'] = trim($values['app_secret']);
    $values['owner_id'] = trim($values['owner_id']);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    $values = &$form_state['values'];
    $config = \Drupal::config('vksync.settings')
      ->set('vksync_app_id', $values['app_id'])
      ->set('vksync_app_secret', $values['app_secret'])
      ->set('vksync_owner_id', $values['owner_id'])
      ->set('vksync_wall_owner', $values['wall_owner'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
