<?php
namespace minepark\player;

use minepark\Core;

class Localizer
{
    public const DEFAULT_LANGUAGE_KEY = "ru_RU";
    public const INTERNATIONAL_LANGUAGE_KEY = "en_US";

    private $supportedLanguages;
    private $data;

    public static function translate(string $langKey, string $content) : string
    {
        return strpos($content, "{") !== false
            ? Core::getActive()->getLocalizer()->translateFrom($langKey, $content)
            : Core::getActive()->getLocalizer()->take($langKey, $content) ?? $content;
    }

    public function __construct()
	{
        $this->supportedLanguages = $this->getKeys();

        @mkdir($this->getDirectory());

        $this->initialize();
	}

	public function getCore() : Core
	{
		return Core::getActive();
    }
    
    public function getLanguages() : array
	{
		return [
            "ru_RU" => "Русский",
            "en_US" => "English",
            "uk_UA" => "Український"
        ];
    }

    public function getKeys() : array
	{
		return array_keys($this->getLanguages());
    }

    public function getLangData() : array
	{
		return $this->data;
    }

    public function take(string $langKey, string $stringKey) : ?string
	{
        if(!isset($this->data[$langKey])) {
            $langKey = self::INTERNATIONAL_LANGUAGE_KEY;
        }

		return $this->takeValue($langKey, $stringKey);
    }

    public function getKeyArray(string $langKey, string $arrayKey) : ?array
    {
        if(!isset($this->data[$langKey][$arrayKey]) ) {
            return isset($this->data[self::DEFAULT_LANGUAGE_KEY][$arrayKey]) && is_array($this->data[self::DEFAULT_LANGUAGE_KEY][$arrayKey])
                ? $this->data[self::DEFAULT_LANGUAGE_KEY][$arrayKey]
                : null;
        }

        return is_array($this->data[$langKey][$arrayKey]) ? $this->data[$langKey][$arrayKey] : null;
    }

    public function translateFrom(string $langKey, string $text) : string
	{
        $result = "";
        $stringKey = "";

        $pos = 0;
        $write = false;

        while($pos < strlen($text)) {
            $ch = $text[$pos];

            if($ch == "{") {
                $write = true;
            } elseif($ch == "}") {
                $write = false;
                $result .= $this->take($langKey, $stringKey);
                $stringKey = "";
            } else {
                if($write) {
                    $stringKey .= $ch;
                } else {
                    $result .= $ch;
                }
            }

            $pos++;
        }

        return $result;
    }

    public function getFileSource(string $langKey) : string
    {
        return $this->getDirectory() . $langKey . ".json";
    }

    private function getDirectory() : string
    {
        return $this->getCore()->getTargetDirectory() . "lang/";
    }

    private function initialize()
    {
        foreach($this->supportedLanguages as $langKey) {
            $file = $this->getFileSource($langKey);

            if(file_exists($file)) {
                $content = file_get_contents($file);
                $this->data[$langKey] = json_decode($content, true);
            } else {
                file_put_contents($file, "{}");
                $this->data[$langKey] = [];
            }
        }
    }

    private function takeValue(string $langKey, string $stringKey) : ?string
    {
        if(!isset($this->data[$langKey][$stringKey])) {
            return isset($this->data[self::DEFAULT_LANGUAGE_KEY][$stringKey])
                ? $this->data[self::DEFAULT_LANGUAGE_KEY][$stringKey]
                : null;
        }

        return $this->data[$langKey][$stringKey];
    }
}
?>