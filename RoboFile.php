<?php

include '.tk/RoboFileBase.php';

class RoboFile extends RoboFileBase {
	public function directoriesStructure() {
		return array('languages' );
	}

	public function fileStructure() {
		return array( 'loader.php', 'composer.json', 'LICENSE', 'readme.txt','loco.xml' );
	}

	public function cleanPhpDirectories() {
		return array();
	}

	public function pluginMainFile() {
		return 'loader';
	}

	public function pluginFreemiusId() {
		return 3326;
	}

	public function minifyAssetsDirectories() {
		return array();
	}

	public function minifyImagesDirectories() {
		return array();
	}

	/**
	 * @return array Pair list of sass source directory and css target directory
	 */
	public function sassSourceTarget() {
		return array( array( 'scss/source' => 'assets/css' ) );
	}

	/**
	 * @return string Relative paths from the root folder of the plugin
	 */
	public function sassLibraryDirectory() {
		return 'scss/library';
	}
}