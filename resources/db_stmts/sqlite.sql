-- #! sqlite

-- #{ ranked
-- #  { init
-- #    { ranks
CREATE TABLE IF NOT EXISTS Ranks(
    id INTEGER PRIMARY KEY,
    name TEXT UNIQUE NOT NULL
);
-- #    }
-- #    { inheritance
CREATE TABLE IF NOT EXISTS Inheritance(
    child_id INTEGER,
    parent_id INTEGER,
    PRIMARY KEY(child_id, parent_id)
);
-- #    }
-- #    { permissions
CREATE TABLE IF NOT EXISTS RankPermissions(
    rank_id INT,
    permission VARCHAR(32),
    PRIMARY KEY(rank_id, permission),
    FOREIGN KEY(rank_id)
        REFERENCES Ranks(id)
        ON DELETE CASCADE
);
-- #    }
-- #    { players
CREATE TABLE IF NOT EXISTS Players(
    uuid VARCHAR(50) NOT NULL,
    username VARCHAR(32) NOT NULL,
    display_name VARCHAR(32) NOT NULL,
    PRIMARY KEY(uuid)
);
-- #    }
-- #    { rank_instances
CREATE TABLE IF NOT EXISTS RankInstances(
    rank_id INT NOT NULL,
    player_uuid VARCHAR(50) NOT NULL,
    expiration_date DATETIME,
    PRIMARY KEY(rank_id, player_uuid),
    FOREIGN KEY(rank_id)
        REFERENCES Ranks(id)
        ON DELETE CASCADE,
    FOREIGN KEY(player_uuid)
        REFERENCES Players(uuid)
        ON DELETE CASCADE
);
-- #    }
-- #  }
-- #  { query
-- #    { ranks_of_player
-- #      :player_uuid string
SELECT ri.rank_id, ri.player_uuid, ri.expiration_date,
       r.name AS rank_name,
       p.username, p.display_name
FROM RankInstances ri
JOIN Players p
ON ri.player_uuid = p.uuid
JOIN Ranks r
ON ri.rank_id = r.id
WHERE ri.player_uuid = :player_uuid;
-- #    }
-- #    { players_of_rank
-- #      :rank_id int
SELECT ri.rank_id, ri.player_uuid, ri.expiration_date,
       r.name AS rank_name,
       p.username, p.display_name
FROM RankInstances ri
JOIN Players p
ON ri.player_uuid = p.uuid
JOIN Ranks r
ON ri.rank_id = r.id
WHERE ri.rank_id = :rank_id;
-- #    }
-- #  }
-- #  { inheritance
-- #    { create
-- #      :child_id int
-- #      :parent_id int
INSERT INTO Inheritance (child_id, parent_id) VALUES (
    :child_id, :parent_id
);
-- #    }
-- #    { remove
-- #      :child_id int
-- #      :parent_id int
DELETE FROM Inheritance
WHERE child_id=:child_id
    AND parent_id=:parent_id;
-- #    }
-- #    { list
SELECT * FROM Inheritance;
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
SELECT id, name FROM Ranks;
-- #    }
-- #  }
-- #  { permissions
-- #    { create
-- #      :rank_id int
-- #      :permission string
REPLACE INTO RankPermissions VALUES(
    :rank_id, :permission
);
-- #    }
-- #    { remove
-- #      :rank_id int
-- #      :permission string
DELETE FROM RankPermissions ri
WHERE ri.rank_id = :rank_id
    AND ri.permission = :permission;
-- #    }
-- #    { list
-- #      :rank_id int
SELECT ri.permission
FROM RankPermissions ri
WHERE ri.rank_id = :rank_id;
-- #    }
-- #  }
-- #  { players
-- #    { create
-- #      :player_uuid string
-- #      :username string
-- #      :display_name string
REPLACE INTO Players(uuid, username, display_name) VALUES(
    :player_uuid, :username, :display_name
);
-- #    }
-- #    { remove
-- #      :player_uuid string
DELETE FROM Players p
WHERE p.uuid = :player_uuid;
-- #    }
-- #    { list
SELECT uuid, username, display_name
FROM Players p;
-- #    }
-- #  }
-- #  { rank_instances
-- #    { create
-- #      :rank_id int
-- #      :player_uuid string
-- #      :expiration_date string
REPLACE INTO RankInstances(rank_id, player_uuid, expiration_date) VALUES(
    :rank_id, :player_uuid, :expiration_date
);
-- #    }
-- #    { remove
-- #      :rank_id int
-- #      :player_uuid string
DELETE FROM RankInstances ri
WHERE ri.rank_id = :rank_id
    AND ri.player_uuid = :player_uuid;
-- #    }
-- #    { clean_expired
-- #      :time string
DELETE FROM RankInstances ri
WHERE ri.expiration_date IS NOT NULL
    AND ri.expiration_date <= :time
-- #    }
-- #  }
-- #}
