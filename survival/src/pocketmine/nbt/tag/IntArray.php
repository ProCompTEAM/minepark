<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;
use pocketmine\utils\Binary;

class IntArray extends NamedTag{

	public function getType(){
		return NBT::TAG_IntArray;
	}

	public function read(NBT $nbt, $new = false){
		$this->value = [];
		if ($new) {
			$size = $nbt->getNewInt();
			for ($i = 0; $i < $size; $i++) {
				$this->value[] = $nbt->getNewInt();
			}
		} else {
			$size = $nbt->endianness === 1 ? Binary::readInt($nbt->get(4)) : Binary::readLInt($nbt->get(4));
			$this->value = array_values(unpack($nbt->endianness === NBT::LITTLE_ENDIAN ? "V*" : "N*", $nbt->get($size * 4)));
		}
	}

	public function write(NBT $nbt, $old = false){
		if ($old) {
			$nbt->buffer .= $nbt->endianness === 1 ? pack("N", \count($this->value)) : pack("V", \count($this->value));
			$nbt->buffer .= pack($nbt->endianness === NBT::LITTLE_ENDIAN ? "V*" : "N*", ...$this->value);
		} else {
			$nbt->putInt(count($this->value));
			foreach ($this->value as $value) {
				$nbt->putInt($value);
			}
		}
	}
}