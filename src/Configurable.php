<?php

declare(strict_types=1);

namespace davekok\wiring;

use Countable;

/**
 * Configurable wireables are wireables that accept other wireables (from other components)
 * to be configured with. So component can integrate with each other.
 *
 * Example:
 *
 * If you have a virtual file system component you may wish to support mounts so other components
 * can expose virtual file trees.
 *
 *     class VirtualFileSystemWireable extends Configurable
 *     {
 *         private readonly VirtualFileSystem $vfs; // to be created when wire is called
 *         private array $mounts; // the mounts to support
 *
 *         public function count(): int
 *         {
 *             return count($this->mounts);
 *         }
 *
 *         public function set(string $mount, Wireable $wireable): static
 *         {
 *             $this->mounts[$mount] = $wireable;
 *             return $this;
 *         }
 *
 *         public function get(string $mount): Wireable
 *         {
 *             return $this->mounts[$mount];
 *         }
 *
 *         public function all(): array
 *         {
 *             return $this->mounts;
 *         }
 *
 *         public function wire(): VirtualFileSystem
 *         {
 *             return $this->vfs ??= new VirtualFileSystem($this->wireMounts());
 *         }
 *
 *         private function wireMounts(): array
 *         {
 *             $mounts = [];
 *             foreach ($this->mounts as $mount => $wireable) {
 *                 $vft = $wireable->wire();
 *                 if ($vft instanceof VirtualFileTree === false) {
 *                     throw new WiringException("Not a VirtualFileTree");
 *                 }
 *                 $mounts[$mount] = $vft;
 *             }
 *             return $mounts;
 *         }
 *     }
 */
interface Configurable extends Wireable, Countable
{
    public function set(string $key, Wireable $wireable): static;
    public function get(string $key): Wireable;
    public function all(): array;

    // inherited from Countable:
    // public function count(): int;

    // inherited from Wireable:
    // public function wire();
}
