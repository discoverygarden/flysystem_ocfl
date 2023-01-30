<?php

/**
 * @file
 * Misc hook definitions.
 */

/**
 * Allow for the altering of layout plugin definitions.
 *
 * @param array $plugins
 *   A reference to the associative array of plugin definitions, mapping plugin
 *   IDs to definitions. Definitions are likely something like:
 *   - id: The ID again (should match the key)
 *   - class: The class to instantiate for the plugin.
 *   - provider: The name of the module/extension providing the plugin.
 */
function hook_flysystem_ocfl_layout_info_alter(array &$plugins) : void {
  // Swap classes, or something?
}
