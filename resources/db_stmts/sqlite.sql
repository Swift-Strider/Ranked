-- #! sqlite

-- #{ ranked
-- #  { init
-- #    { ranks
CREATE TABLE IF NOT EXISTS Ranks(
    id INTEGER PRIMARY KEY,
    name TEXT UNIQUE NOT NULL
);
-- #    }
-- #    { rankpermissions
CREATE TABLE IF NOT EXISTS RankPermissions(
    rank_id INTEGER NOT NULL,
    permission TEXT NOT NULL,
    PRIMARY KEY(rank_id, permission),
    FOREIGN KEY(rank_id)
        REFERENCES Ranks(id)
        ON DELETE CASCADE
);
-- #    }
-- #    { players
CREATE TABLE IF NOT EXISTS Players(
    player_uuid VARCHAR(50) NOT NULL,
    username TEXT NOT NULL,
    display_name TEXT NOT NULL,
    PRIMARY KEY(player_uuid)
);
-- #    }
-- #    { rank_players
CREATE TABLE IF NOT EXISTS RankPlayers(
    rank_id INTEGER NOT NULL,
    player_uuid VARCHAR(50) NOT NULL,
    expiraton_date DATETIME NOT NULL,
    PRIMARY KEY(rank_id, player_uuid),
    FOREIGN KEY(rank_id)
        REFERENCES Ranks(id)
        ON DELETE CASCADE,
    FOREIGN KEY(player_uuid)
        REFERENCES Players(player_uuid)
        ON DELETE CASCADE
);
-- #    }
-- #  }
-- #  { ranks
-- #    { create
-- #      :name string
INSERT INTO Ranks(name) VALUES(
    :name
);
-- #    }
-- #    { remove
-- #      :id int
DELETE FROM Ranks
WHERE id = :id;
-- #    }
-- #    { list
SELECT * FROM Ranks;
-- #    }
-- #    { get
-- #      :name string
SELECT id FROM Ranks
WHERE name = :name;
-- #    }
-- #  }
-- #  { permissions
-- #    { set
-- #      :rank_id int
-- #      :permission string
REPLACE INTO RankPermissions VALUES(
    :rank_id, :permission
);
-- #    }
-- #    { unset
-- #      :rank_id int
-- #      :permission string
DELETE FROM RankPermissions rp
WHERE rp.rank_id = :rank_id
    AND rp.permission = :permission;
-- #    }
-- #    { list
-- #      :rank_id int
SELECT rp.permission
FROM RankPermissions rp
WHERE rp.rank_id = :rank_id;
-- #    }
-- #  }
-- #  { players
-- #    { set
-- #      :player_uuid string
-- #      :username string
-- #      :display_name string
REPLACE INTO Players VALUES(
    :player_uuid, :username, :display_name
);
-- #    }
-- #    { unset
-- #      :player_uuid string
DELETE FROM Players p
WHERE p.player_uuid = :player_uuid;
-- #    }
-- #    { list
SELECT * FROM Players p;
-- #    }
-- #  }
-- #  { rank_players
-- #    { set
-- #      :rank_id int
-- #      :player_uuid string
REPLACE INTO RankPlayers VALUES(
    :rank_id, :player_uuid
);
-- #    }
-- #    { unset
-- #      :rank_id int
-- #      :player_uuid string
DELETE FROM RankPlayers rp
WHERE rp.rank_id = :rank_id
    AND rp.player_uuid = :player_uuid;
-- #    }
-- #    { clean_expired
-- #      :time string
DELETE FROM RankPlayers rp
WHERE rp.expiration_date < :time
-- #    }
-- #    { list_ranks
-- #      :player_uuid string
SELECT rp.rank_id
FROM RankPlayers rp
WHERE rp.player_uuid = :player_uuid;
-- #    }
-- #    { list_players
-- #      :rank_id int
SELECT rp.player_uuid, p.username, p.display_name
FROM RankPlayers rp
JOIN Players p
ON rp.player_uuid = p.player_uuid
WHERE rp.rank_id = :rank_id;
-- #    }
-- #  }
-- #}
