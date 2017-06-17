<?php

namespace dnl_blkv\alcdef;

/**
 */
class AlcdefItem
{
    /**
     * Newline values to use in our ALCDEF strings.
     */
    const PATTERN_NEW_LINE_ANY = '@\\r?\\n@';
    const NEW_LINE_UNIX = "\n";

    /**
     * Delimiters to split ALCDEF document into lines.
     */
    const DELIMITER_ALCDEF_LINES = self::NEW_LINE_UNIX;

    /**
     * ALCDEF field names.
     */
    const FIELD_DATA = 'DATA';
    const FIELD_OBJECT_NAME = 'OBJECTNAME';

    /**
     * Constants to split an ALCDEF unit into data and metadata.
     */
    const PATTERN_ALCDEF_METADATA_DATA = '@STARTMETADATA\\n(.*)\\nENDMETADATA\\n(.*)\\n@ms';
    const SUBMATCH_INDEX_METADATA = 1;
    const SUBMATCH_INDEX_DATA = 2;

    /**
     * Constants to fetch key and value from the ACLDEF line.
     */
    const DELIMITER_ALCDEF_KEY_VALUE = '=';
    const PART_COUNT_ALCDEF_LINE = 2;
    const ALCDEF_LINE_PART_INDEX_KEY = 0;
    const ALCDEF_LINE_PART_INDEX_VALUE = 1;

    /**
     * @var mixed[]
     */
    private $alcdefArray = [];

    /**
     * @param string $alcdefString
     */
    public function __construct($alcdefString)
    {
        $this->loadAlcdefArray($alcdefString);
    }

    /**
     * @param string $alcdefString
     */
    private function loadAlcdefArray($alcdefString)
    {
        $alcdefString = $this->normalizeNewlines($alcdefString);
        preg_match(self::PATTERN_ALCDEF_METADATA_DATA, $alcdefString, $alcdefParts);
        $this->loadAlcdefMetadata($alcdefParts[self::SUBMATCH_INDEX_METADATA]);
        $this->loadAlcdefData($alcdefParts[self::SUBMATCH_INDEX_DATA]);
    }

    /**
     * @param $string
     *
     * @return string
     */
    private function normalizeNewlines($string)
    {
        return preg_replace(self::PATTERN_NEW_LINE_ANY, self::NEW_LINE_UNIX, $string);
    }

    /**
     * @param string $alcdefMetadataString
     */
    private function loadAlcdefMetadata($alcdefMetadataString)
    {
        foreach (explode(self::DELIMITER_ALCDEF_LINES, $alcdefMetadataString) as $line) {
            $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $line, self::PART_COUNT_ALCDEF_LINE);
            $this->alcdefArray[$field[self::ALCDEF_LINE_PART_INDEX_KEY]] = $field[self::ALCDEF_LINE_PART_INDEX_VALUE];
        }
    }

    /**
     * @param string $alcdefDataString
     */
    private function loadAlcdefData($alcdefDataString)
    {
        $this->alcdefArray[self::FIELD_DATA] = [];

        foreach (explode(self::DELIMITER_ALCDEF_LINES, $alcdefDataString) as $line) {
            $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $line, self::SUBMATCH_INDEX_DATA);
            $this->alcdefArray[self::FIELD_DATA][] = $field[self::SUBMATCH_INDEX_METADATA];
        }
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return $this->getFieldByName(self::FIELD_OBJECT_NAME);
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    private function getFieldByName($name)
    {
        if (isset($this->alcdefArray[$name])) {
            return $this->alcdefArray[$name];
        } else {
            return null;
        }
    }
}
