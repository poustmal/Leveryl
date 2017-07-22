<?php

/*
 *
 *  ____			_		_   __  __ _				  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___	  |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|	 |_|  |_|_|
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

declare(strict_types = 1);

namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Snowball extends Projectile
{
	const NETWORK_ID = 81;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.03;
	protected $drag = 0.01;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null)
	{
		parent::__construct($level, $nbt, $shootingEntity);
	}

	public function onUpdate($currentTick)
	{
		if($this->closed) {
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if($this->age > 1200 or $this->isCollided) {
			if($this->isAlive()) {
				$this->getLevel()->addParticle(new DestroyBlockParticle($this, Block::get(Block::SNOW)));
				$this->kill();
				$hasUpdate = true;
			}
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	public function spawnTo(Player $player)
	{
		$pk = new AddEntityPacket();
		$pk->type = Snowball::NETWORK_ID;
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}
