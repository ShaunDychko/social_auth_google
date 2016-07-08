<?php

namespace Drupal\social_auth_google\Plugin\Network;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\MetadataBubblingUrlGenerator;
use Drupal\social_api\Plugin\NetworkBase;
use Drupal\social_api\SocialApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Network(
 *   id = "social_auth_google",
 *   social_network = "Google",
 *   type = "social_auth",
 *   handlers = {
 *      "settings": {
 *          "class": "\Drupal\social_auth_google\Settings\GoogleAuthSettings",
 *          "config_id": "social_auth_google.settings"
 *      }
 *   }
 * )
 */
class GoogleAuth extends NetworkBase {
  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('url_generator'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * GoogleLogin constructor.
   *
   * @param \Drupal\Core\Render\MetadataBubblingUrlGenerator $url_generator
   * @param array $configuration
   * @param mixed $plugin_id
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(MetadataBubblingUrlGenerator $url_generator, array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $config_factory);

    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  protected function initSdk() {
    $class_name = '\Google_Client';
    if (!class_exists($class_name)) {
      throw new SocialApiException(sprintf('The PHP SDK for Google Services could not be found. Class: %s.', $class_name));
    }

    /* @var \Drupal\social_auth_google\Settings\GoogleAuthSettings $settings */
    $settings = $this->settings;

    // Gets the absolute url of the callback
    $redirect_uri = $this->urlGenerator->generateFromRoute('social_auth_google.callback', array() ,array('absolute' => TRUE));

    // Creates a and sets data to Google_Client object
    $client = new \Google_Client();
    $client->setClientId($settings->getClientId());
    $client->setClientSecret($settings->getClientSecret());
    $client->setRedirectUri($redirect_uri);

    return $client;
  }
}