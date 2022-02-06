<?php

declare(strict_types=1);

namespace DiamondStrider1\Ranked\database;

use Closure;
use Generator;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;
use SOFe\RwLock\Mutex;

class QueryRunner
{
    private ?Mutex $sqliteLock;

    public function __construct(
        private DataConnector $db,
        bool $isSqlite,
    ) {
        $this->sqliteLock = $isSqlite ? new Mutex() : null;
    }

    public function init(): Generator
    {
        yield from $this->lock(function (): Generator {
            $this->db->executeGeneric('ranked.init.ranks', [], yield Await::RESOLVE, yield Await::REJECT);
            $this->db->executeGeneric('ranked.init.rankpermissions', [], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ALL;
        });
    }

    public function createRank(string $name): Generator
    {
        return yield from $this->lock(function () use ($name): Generator {
            $this->db->executeInsert('ranked.ranks.create', ['name' => $name], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function removeRank(int $id): Generator
    {
        return yield from $this->lock(function () use ($id): Generator {
            $this->db->executeChange('ranked.ranks.remove', ['id' => $id], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function listRanks(): Generator
    {
        return yield from $this->lock(function (): Generator {
            $this->db->executeSelect('ranked.ranks.list', [], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function getById(string $name): Generator
    {
        return yield from $this->lock(function () use ($name): Generator {
            $this->db->executeSelect('ranked.ranks.get', ['name' => $name], yield Await::RESOLVE, yield Await::REJECT);

            return (yield Await::ONCE)[0]['id'] ?? null;
        });
    }

    public function setPermission(int $rankId, string $permission): Generator
    {
        yield from $this->lock(function () use ($rankId, $permission): Generator {
            $this->db->executeGeneric('ranked.permissions.set', ['rank_id' => $rankId, 'permission' => $permission], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function unsetPermission(int $rankId, string $permission): Generator
    {
        return yield from $this->lock(function () use ($rankId, $permission): Generator {
            $this->db->executeGeneric('ranked.permissions.unset', ['rank_id' => $rankId, 'permission' => $permission], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function listPermissions(int $rankId): Generator
    {
        return yield from $this->lock(function () use ($rankId): Generator {
            $this->db->executeSelect('ranked.permissions.list', ['rank_id' => $rankId], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    /**
     * @param Closure(): Generator $closure
     */
    private function lock(Closure $closure): Generator
    {
        if (null !== $this->sqliteLock) {
            return yield from $this->sqliteLock->run($closure());
        }

        return yield from $closure();
    }
}
