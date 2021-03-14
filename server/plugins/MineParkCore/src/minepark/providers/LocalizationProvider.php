<?php
namespace minepark\providers;

use minepark\Core;
use minepark\Providers;
use minepark\defaults\Defaults;
use minepark\providers\base\Provider;

class LocalizationProvider extends Provider
{
    private $supportedLanguages;
    private $data;

    public static function translate(string $langKey, string $content) : string
    {
        return strpos($content, "{") !== false
            ? Providers::getLocalizationProvider()->translateFrom($langKey, $content)
            : Providers::getLocalizationProvider()->take($langKey, $content) ?? $content;
    }

    public function __construct()
	{
        $this->supportedLanguages = $this->getKeys();

        @mkdir($this->getDirectory());

        $this->initialize();
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
            $langKey = Defaults::INTERNATIONAL_LANGUAGE_KEY;
        }

		return $this->takeValue($langKey, $stringKey);
    }

    public function getKeyArray(string $langKey, string $arrayKey) : ?array
    {
        if(!isset($this->data[$langKey][$arrayKey]) ) {
            return isset($this->data[Defaults::DEFAULT_LANGUAGE_KEY][$arrayKey]) and is_array($this->data[Defaults::DEFAULT_LANGUAGE_KEY][$arrayKey])
                ? $this->data[Defaults::DEFAULT_LANGUAGE_KEY][$arrayKey]
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
                $result .= $this->take($langKey, $stringKey) ?? $stringKey;
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
        return Core::getActive()->getTargetDirectory() . "lang/";
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
            return isset($this->data[Defaults::DEFAULT_LANGUAGE_KEY][$stringKey])
                ? $this->data[Defaults::DEFAULT_LANGUAGE_KEY][$stringKey]
                : null;
        }

        return $this->data[$langKey][$stringKey];
    }
}
?>