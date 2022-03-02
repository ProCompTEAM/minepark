<?php
namespace minepark\defaults;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyMetadata;

class VehicleConstants
{
    public const ACTION_BE_DRIVER = 1;
    public const ACTION_BE_PASSENGER = 2;

    public const PLAYER_NEAR_STATION_DISTANCE = 5;

    public const TRAIN_DEFAULT_SPEED = 0.6;

    public const TRAIN_DIRECTION_NORTH = 1;
    public const TRAIN_DIRECTION_SOUTH = 2;
    public const TRAIN_DIRECTION_WEST = 3;
    public const TRAIN_DIRECTION_EAST = 4;

    public static function getDirectionByYaw(int $railDirection, int $yaw) : int
    {
        if($railDirection === BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH) {
            if($yaw === 0) {
                return self::TRAIN_DIRECTION_SOUTH;
            } else if($yaw === 180) {
                return self::TRAIN_DIRECTION_NORTH;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_STRAIGHT_EAST_WEST) {
            if($yaw === 90) {
                return self::TRAIN_DIRECTION_EAST;
            } else if($yaw === 270) {
                return self::TRAIN_DIRECTION_WEST;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_ASCENDING_EAST) {
            if($yaw === 90) {
                return self::TRAIN_DIRECTION_EAST;
            } else if($yaw === 270) {
                return self::TRAIN_DIRECTION_WEST;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_ASCENDING_WEST) {
            if($yaw === 270) {
                return self::TRAIN_DIRECTION_WEST;
            } else if($yaw === 90) {
                return self::TRAIN_DIRECTION_EAST;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_ASCENDING_NORTH) {
            if($yaw === 0) {
                return self::TRAIN_DIRECTION_SOUTH;
            } else if($yaw === 180) {
                return self::TRAIN_DIRECTION_NORTH;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST) {
            if($yaw === 45) {
                return self::TRAIN_DIRECTION_SOUTH;
            } else if($yaw === 225) {
                return self::TRAIN_DIRECTION_EAST;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST) {
            if($yaw === 315) {
                return self::TRAIN_DIRECTION_SOUTH;
            } else if($yaw === 135) {
                return self::TRAIN_DIRECTION_WEST;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_CURVE_NORTHWEST) {
            if($yaw === 225) {
                return self::TRAIN_DIRECTION_NORTH;
            } else if($yaw === 45) {
                return self::TRAIN_DIRECTION_WEST;
            }
        } else if($railDirection === BlockLegacyMetadata::RAIL_CURVE_NORTHEAST) {
            if($yaw === 135) {
                return self::TRAIN_DIRECTION_NORTH;
            } else if($yaw === 315) {
                return self::TRAIN_DIRECTION_EAST;
            }
        }

        return -1;
    }

    public static function getRailRotations(int $direction) : array
    {
        if($direction === self::TRAIN_DIRECTION_NORTH) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH => 0,
                BlockLegacyMetadata::RAIL_ASCENDING_NORTH => 0,
                BlockLegacyMetadata::RAIL_ASCENDING_SOUTH => 0,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => 45,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => 315,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => 135,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => 225
            ];
        } elseif($direction === self::TRAIN_DIRECTION_SOUTH) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH => 180,
                BlockLegacyMetadata::RAIL_ASCENDING_SOUTH => 180,
                BlockLegacyMetadata::RAIL_ASCENDING_NORTH => 180,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => 45,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => 315,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => 135,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => 225
            ];
        } elseif($direction === self::TRAIN_DIRECTION_WEST) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_EAST_WEST => 270,
                BlockLegacyMetadata::RAIL_ASCENDING_WEST => 270,
                BlockLegacyMetadata::RAIL_ASCENDING_EAST => 270,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => 225,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => 315,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => 135,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => 45
            ];
        } elseif($direction === self::TRAIN_DIRECTION_EAST) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_EAST_WEST => 90,
                BlockLegacyMetadata::RAIL_ASCENDING_EAST => 90,
                BlockLegacyMetadata::RAIL_ASCENDING_WEST => 90,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => 225,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => 315,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => 135,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => 45
            ];
        }

        return [];
    }

    public static function getRailDirections(int $direction) : array
    {
        if($direction === self::TRAIN_DIRECTION_NORTH) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH => self::TRAIN_DIRECTION_NORTH,
                BlockLegacyMetadata::RAIL_ASCENDING_NORTH => self::TRAIN_DIRECTION_NORTH,
                BlockLegacyMetadata::RAIL_ASCENDING_SOUTH => self::TRAIN_DIRECTION_NORTH,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => self::TRAIN_DIRECTION_EAST,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => self::TRAIN_DIRECTION_WEST,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => self::TRAIN_DIRECTION_EAST,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => self::TRAIN_DIRECTION_WEST
            ];
        } else if($direction === self::TRAIN_DIRECTION_SOUTH) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH => self::TRAIN_DIRECTION_SOUTH,
                BlockLegacyMetadata::RAIL_ASCENDING_SOUTH => self::TRAIN_DIRECTION_SOUTH,
                BlockLegacyMetadata::RAIL_ASCENDING_NORTH => self::TRAIN_DIRECTION_SOUTH,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => self::TRAIN_DIRECTION_EAST,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => self::TRAIN_DIRECTION_WEST,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => self::TRAIN_DIRECTION_EAST,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => self::TRAIN_DIRECTION_WEST
            ];
        } else if($direction === self::TRAIN_DIRECTION_WEST) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_EAST_WEST => self::TRAIN_DIRECTION_WEST,
                BlockLegacyMetadata::RAIL_ASCENDING_WEST => self::TRAIN_DIRECTION_WEST,
                BlockLegacyMetadata::RAIL_ASCENDING_EAST => self::TRAIN_DIRECTION_WEST,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => self::TRAIN_DIRECTION_SOUTH,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => self::TRAIN_DIRECTION_NORTH,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => self::TRAIN_DIRECTION_SOUTH,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => self::TRAIN_DIRECTION_NORTH
            ];
        } else if($direction === self::TRAIN_DIRECTION_EAST) {
            return [
                BlockLegacyMetadata::RAIL_STRAIGHT_EAST_WEST => self::TRAIN_DIRECTION_EAST,
                BlockLegacyMetadata::RAIL_ASCENDING_EAST => self::TRAIN_DIRECTION_EAST,
                BlockLegacyMetadata::RAIL_ASCENDING_WEST => self::TRAIN_DIRECTION_EAST,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHEAST => self::TRAIN_DIRECTION_SOUTH,
                BlockLegacyMetadata::RAIL_CURVE_NORTHEAST => self::TRAIN_DIRECTION_NORTH,
                BlockLegacyMetadata::RAIL_CURVE_SOUTHWEST => self::TRAIN_DIRECTION_SOUTH,
                BlockLegacyMetadata::RAIL_CURVE_NORTHWEST => self::TRAIN_DIRECTION_NORTH
            ];
        }

        return [];
    }
}