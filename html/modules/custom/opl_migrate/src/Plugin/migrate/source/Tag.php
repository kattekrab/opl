<?php 
namespace Drupal\opl_migrate\Plugin\migrate\source;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Row;

/** 
* Source plugin to import practices from YAML files
* @MigrateSource(
*   id = "tag"
* )
*/
class Tag extends SourcePluginBase {

  public function getIds() {
    $ids = [
      'yaml_filename' => [
        'type' => 'string'
      ]
    ];
    return $ids;
  }

  public function fields() {
    return [
      'name' => $this->t('Name'),
      'yaml_filename' => $this->t("Source YAML filename"),
    ]; 
  }

  public function __toString() {
    return "yaml data";
  }

  /**
   * Initializes the iterator with the source data.
   * @return \Iterator
   *   An iterator containing the data for this source.
   */
  protected function initializeIterator() {

    // Loop through the source files and find anything with a .md extension.
    $path = "sites/default/private/openpracticelibrary/src/pages/practice/*.md";
    $filenames = glob($path);
    $rows = [];
    foreach ($filenames as $filename) {
      // Using second argument of TRUE here because migrate needs the data to be
      // associative arrays and not stdClass objects.
      $data = yaml_parse_file($filename);
      if (!empty($data['tags'])) {
        foreach ($data['tags'] as $i => $tag) {
          $row['yaml_filename'] = $filename;
          $row['name'] = $tag;
          // Append it to the array of rows we can import.
          $rows[$tag] = $row;
        }
      }
    }

    // Migrate needs an Iterator class, not just an array
    return new \ArrayIterator($rows);
  }
}
