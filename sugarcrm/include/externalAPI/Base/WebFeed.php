<?php
interface WebFeed {
	public function getLatestUpdates($maxTime, $maxEntries);
}