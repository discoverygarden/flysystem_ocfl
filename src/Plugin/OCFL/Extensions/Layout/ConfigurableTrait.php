<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

/**
 * Configurability helper trait.
 */
trait ConfigurableTrait {

  /**
   * Config array being dealt with.
   *
   * @var array
   *
   * @see \Drupal\Core\Plugin\PluginBase::$configuration
   */
  protected $configuration;

  /**
   * Gets the array of configuration.
   *
   * @see \Drupal\Component\Plugin\ConfigurableInterface::getConfiguration()
   *
   * @return array
   *   The array of configuration.
   */
  public function getConfiguration() : array {
    return $this->configuration;
  }

  /**
   * Sets the array of configuration.
   *
   * @param array $configuration
   *   The array of config to set.
   *
   * @see \Drupal\Component\Plugin\ConfigurableInterface::setConfiguration()
   */
  public function setConfiguration(array $configuration) : void {
    $to_set = $configuration + $this->defaultConfiguration();
    $this->validateConfiguration($to_set);
    $this->configuration = $to_set;
  }

  /**
   * Allow users to add some basic validation, expecting exceptions.
   *
   * @param array $configuration
   *   The array of configuration to be validated.
   */
  protected function validateConfiguration(array $configuration) : void {
    // Default is no-op.
  }

  /**
   * Get default configuration.
   *
   * @see \Drupal\Component\Plugin\ConfigurableInterface::defaultConfiguration()
   *
   * @return array
   *   The default configuration.
   */
  public function defaultConfiguration() : array {
    return [
      'extensionName' => $this->getPluginId(),
    ] + $this->defaultPluginConfiguration();
  }

  /**
   * Extension point; allow users to add in other bits of configuration.
   *
   * Otherwise, would be necessary to deal with aliasing the method from the
   * traits, so let's avoid such copypasta.
   *
   * @return array
   *   Default specific config.
   *
   * @see \Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\ConfigurableTrait::defaultConfiguration()
   */
  protected function defaultPluginConfiguration() : array {
    return [];
  }

  /**
   * Get the plugin ID.
   *
   * @see \Drupal\Core\Plugin\PluginBase::getPluginId()
   *
   * @return string
   *   The ID of the plugin.
   */
  abstract public function getPluginId();

  /**
   * Load up our config.
   *
   * @param string $root
   *   Path of the OCFL storage root.
   *
   * @return $this
   *   The current instance.
   *
   * @see \Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\ConfigurableInterface::loadConfiguration()
   */
  public function loadConfiguration(string $root) {
    $extension = $this->getPluginId();
    $extension_config_path = "{$root}/extensions/{$extension}/config.json";
    assert(file_exists($extension_config_path));

    $this->setConfiguration(json_decode(file_get_contents($extension_config_path), TRUE));

    return $this;
  }

}
