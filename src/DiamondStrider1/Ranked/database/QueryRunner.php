<?php

declare(strict_types=1);

namespace DiamondStrider1\Ranked\database;

use Generator;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;

class QueryRunner
{
    public function __construct(
        private DataConnector $db,
    ) {
    }

    public function init(): Generator
    {
        $this->db->executeGeneric('ranked.init.ranks', [], yield Await::RESOLVE, yield Await::REJECT);
        $this->db->executeGeneric('ranked.init.rankpermissions', [], yield Await::RESOLVE, yield Await::REJECT);
        yield Await::ALL;
    }

    public function createRank(string $name): Generator
    {
        $this->db->executeInsert('ranked.ranks.create', ['name' => $name], yield Await::RESOLVE, yield Await::REJECT);

        return yield Await::ONCE;
    }

    public function removeRank(int $id): Generator
    {
        $this->db->executeChange('ranked.ranks.remove', ['id' => $id], yield Await::RESOLVE, yield Await::REJECT);

        return yield Await::ONCE;
    }

    public function listRanks(): Generator
    {
        $this->db->executeSelect('ranked.ranks.list', [], yield Await::RESOLVE, yield Await::REJECT);

        return yield Await::ONCE;
    }

    public function getById(string $name): Generator
    {
        $this->db->executeSelect('ranked.ranks.get', ['name' => $name], yield Await::RESOLVE, yield Await::REJECT);

        return (yield Await::ONCE)[0]['id'] ?? null;
    }

    public function setPermission(int $rankId, string $permission): Generator
    {
        $this->db->executeGeneric('ranked.permissions.set', ['rank_id' => $rankId, 'permission' => $permission], yield Await::RESOLVE, yield Await::REJECT);
        yield Await::ONCE;
    }

    public function unsetPermission(int $rankId, string $permission): Generator
    {
        $this->db->executeGeneric('ranked.permissions.unset', ['rank_id' => $rankId, 'permission' => $permission], yield Await::RESOLVE, yield Await::REJECT);
        yield Await::ONCE;
    }

    public function listPermissions(int $rankId): Generator
    {
        $this->db->executeSelect('ranked.permissions.list', ['rank_id' => $rankId], yield Await::RESOLVE, yield Await::REJECT);

        return yield Await::ONCE;
    }

    public function listRankPermissionPairs(): Generator
    {
        $this->db->executeSelect('ranked.permissions.list_all', [], yield Await::RESOLVE, yield Await::REJECT);

        return yield Await::ONCE;
    }
}
