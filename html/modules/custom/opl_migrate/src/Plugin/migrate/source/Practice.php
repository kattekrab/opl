<?php 
namespace Drupal\opl_migrate\Plugin\migrate\source;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Row;

/** 
* Source plugin to import practices from YAML files
* @MigrateSource(
*   id = "practice"
* )
*/
class Practice extends SourcePluginBase {

  public function prepareRow(Row $row) {
    $title = $row->getSourceProperty('title');
    // Make sure the title isn't too long for Drupal.
    if (strlen($title) > 255) {
      $row->setSourceProperty('title', substr($title,0,255));
    }
    return parent::prepareRow($row); 
  }

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
      'url' => $this->t('URL'),
      'title' => $this->t('Title'),
      'subtitle' => $this->t('Subtitle'),
      'howTo' => $this->t('How To'),
      'whatIs' => $this->t('What Is'),
      'whyDo' => $this->t('Why Do'),
      'howTo' => $this->t('How To'),
      'time' => $this->t('Time'),
      'date' => $this->t('Date Published'),
      'tags' => $this->t('Tags'),
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
      $row = yaml_parse_file($filename); // Sets the title, body, etc.
      $row['yaml_filename'] = $filename;

      // Migrate needs the date as a UNIX timestamp.
      try {
        // put your source data's time zone here, or just use strtotime() if it's already in UTC.
        $d = new \DateTime($row['date'], new \DateTimeZone('Etc/UTC'));
        $row['date'] = $d->format('U');
      } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
        $row['date'] = time();  // Fallback – set it to now so we don't have errors.
      }

      // append it to the array of rows we can import.
      $rows[] = $row;
    }

    // Migrate needs an Iterator class, not just an array
    return new \ArrayIterator($rows);
  }
}
