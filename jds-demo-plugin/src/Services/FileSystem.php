<?php

namespace JdsDemoPlugin\Services;

use FilesystemIterator;
use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
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
	const PATH_SEPARATORS = '/\\';
	private string $root;
	private int $rootLength;

	/** @noinspection PhpUnused */
	public static function deleteAllFilter(): bool {
		return true;
	}

	public static function deleteAllButGitignore( SplFileInfo $fileInfo ): bool {
		return '.gitignore' !== $fileInfo->getFilename();
	}

	public function forceTrailingSlash( $path ): string {
		return rtrim( $path, self::PATH_SEPARATORS ) . '/';
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
		if ( preg_match( '/^(\/)$|^([a-z]+:\/)$/i', $realRoot ) ) {
			trigger_error( "FileSystem created with the file system as a root path ($realRoot)", E_USER_WARNING );
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

	private function createFileInfoIterator( $path ): RecursiveIteratorIterator {
		return new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
	}

	/**
	 * Clear a directory of files
	 *
	 * An optional predicate (filter) callback can determine which files, if any, to keep.
	 *
	 * The predicate will receive a `SplFileInfo` object as a parameter.
	 *
	 * @throws InvalidArgumentException
	 * @throws CommandFailureException
	 * @see FileSystem::deleteAllButGitignore an example filter implementation
	 */
	public function emptyDirectory( string $path, ?callable $predicate = null ): void {
		$path      = $this->toAbsoluteSafePath( $path );
		$predicate = $predicate ?? [ FileSystem::class, 'deleteAllFilter' ];
		$iterator  = $this->createFileInfoIterator( $path );
		/** @var SplFileInfo $fileInfo */
		foreach ( $iterator as $fileInfo ) {
			if ( $predicate( $fileInfo ) ) {
				$result = $fileInfo->isDir()
					? rmdir( $fileInfo->getRealPath() )
					: unlink( $fileInfo->getRealPath() );
				if ( $result !== true ) {
					throw new CommandFailureException( "Unable to delete file or directory: {$fileInfo->getRealPath()}" );
				}
			}
		}
	}

	/**
	 * Process files in a directory
	 *
	 * The delegate will receive all files. Directories will be passed to the delegate
	 * if the $includeDirectories flag is set to true.
	 * @throws InvalidArgumentException
	 */
	public function processFiles( string $path, callable $delegate, bool $includeDirectories = false ): void {
		$path = $this->toAbsoluteSafePath( $path );
		if ( ! is_callable( $delegate ) ) {
			throw new InvalidArgumentException( 'The delegate parameter must be callable' );
		}
		$iterator = $this->createFileInfoIterator( $path );
		/** @var SplFileInfo $fileInfo */
		foreach ( $iterator as $fileInfo ) {
			if ( $fileInfo->isFile() || $includeDirectories ) {
				$delegate( $fileInfo );
			}
		}
	}
}
