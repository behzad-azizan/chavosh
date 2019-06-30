<?php


namespace Azizan\Chavosh;


use Azizan\Chavosh\Exceptions\FileNotFoundException;
use Azizan\Chavosh\Interfaces\PhraseBuilderInterface;

/**
 * @property string|null csvPath
 */
class PhraseBuilder implements PhraseBuilderInterface
{
    public function __construct($csvPath = null)
    {
        if (is_null($csvPath))
            $csvPath = __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'repository.csv';

        if (! file_exists($csvPath))
            throw new FileNotFoundException('The CSV file path is invalid.');

        $this->csvPath = $csvPath;
    }

    /**
     * Get a random Phrase from the CSV file contents
     * @return string
     */
    public function getPhrase()
    {
        $phrases = file($this->csvPath);
        return $phrases[array_rand($phrases)];
    }
}