-- #! sqlite

-- #{ ranked
-- #  { init
-- #    { ranks
CREATE TABLE IF NOT EXISTS Ranks(
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(20) UNIQUE NOT NULL,
    PRIMARY KEY(id)
);
-- #    }
-- #    { rankpermissions
CREATE TABLE IF NOT EXISTS RankPermissions(
    rank_id INT,
    permission VARCHAR(50),
    PRIMARY KEY(rank_id, permission),
    FOREIGN KEY(rank_id)
        REFERENCES Ranks(id)
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
-- #}
