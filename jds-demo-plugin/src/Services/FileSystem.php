<?php

namespace JdsDemoPlugin\Services;

use FilesystemIterator;
use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Plugin;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Common FileSystem interactions
 *
 * All operations must take place within the root path provided at construction.
 *
 * An optional check will try to detect if the provided root path is not the plugin directory
 * or a child of the plugin directory.
 */
class FileSystem {
	private string $root;
	private int $rootLength;

	/** @noinspection PhpUnused */
	public static function deleteAllFilter(): bool {
		return true;
	}

	public static function deleteAllButGitignore( SplFileInfo $fileInfo ): bool {
		return '.gitignore' !== $fileInfo->getFilename();
	}


	/**
	 * @throws InvalidArgumentException
	 */
	public function __construct( string $root, bool $verifyPath = true ) {
		$realRoot = realpath( $root );

		if ( false === $realRoot ) {
			throw new InvalidArgumentException( "The root path passed into FileSystem does not appear to be valid: $root" );
		}

		if ( $verifyPath && 0 !== mb_strpos( $realRoot, dirname( __DIR__, 2 ) ) ) {
			throw new InvalidArgumentException( "The root path passed into FileSystem does not appear to be a parent of the plugin directory: $realRoot" );
		}

		// check to see if the string looks like / or c:/ -- possible file system root paths
		if ( preg_match( '/^(\/)|^([a-z]+:\/)/i', $realRoot ) ) {
			trigger_error( "FileSystem created with the file system as a root path (this is dangerous)", E_USER_WARNING );
		}

		$this->root       = $realRoot;
		$this->rootLength = mb_strlen( $realRoot );
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function toAbsoluteSafePath( string $path ): string {
		$realPath = realpath( $path );
		if ( mb_substr( $realPath, 0, $this->rootLength ) !== $this->root ) {
			throw new InvalidArgumentException( "The FileSystem class can only operate within an allowed root directory. Allowed root path: '{$this->root}'. Attempted target path: '$realPath'" );
		}

		return $realPath;
	}

	/**
	 * Clear a directory of files
	 *
	 * An optional filter can determine which files, if any, to keep.
	 *
	 * The filter will receive a `SplFileInfo` object as a parameter.
	 *
	 * @throws InvalidArgumentException
	 * @throws CommandFailureException
	 * @see FileSystem::deleteAllButGitignore an example filter implementation
	 */
	public function emptyDirectory( string $path, ?callable $filter = null ): void {
		$path   = $this->toAbsoluteSafePath( $path );
		$filter = $filter ?? [ FileSystem::class, 'deleteAllFilter' ];
		$files  = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		/** @var SplFileInfo $fileInfo */
		foreach ( $files as $fileInfo ) {
			if ( $filter( $fileInfo ) ) {
				$result = $fileInfo->isDir()
					? rmdir( $fileInfo->getRealPath() )
					: unlink( $fileInfo->getRealPath() );
				if ( $result !== true ) {
					throw new CommandFailureException( "Unable to delete file or directory: {$fileInfo->getRealPath()}" );
				}
			}
		}
	}
}
