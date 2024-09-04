-- #! sqlite
-- # { homes

    -- # { players

        -- # { init
            CREATE TABLE IF NOT EXISTS players(
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                name        TEXT NOT NULL,
                home_limit  INTEGER DEFAULT 5
            );
        -- # }

        -- # { auth
        -- # :name  string
        -- # :limit int
            INSERT INTO players(name, home_limit) VALUES(:name, :limit)
        -- # }

        -- # { get
        -- # :name string
            SELECT * FROM players
            WHERE name = :name;
        -- # }

        -- # { updateLimit
        -- # :id int
        -- # :limit int
            UPDATE players
            SET home_limit = :limit
            WHERE id = :id;
        -- # }

    -- # }

    -- # { init
        CREATE TABLE IF NOT EXISTS homes(
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT NOT NULL,
            x           FLOAT NOT NULL,
            y           FLOAT NOT NULL,
            z           FLOAT NOT NULL,
            yaw         FLOAT NOT NULL,
            pitch       FLOAT NOT NULL,
            world       VARCHAR(255) NOT NULL DEFAULT "world",
            player_name VARCHAR NOT NULL,
            player_id   INTEGER NOT NULL,
            date      DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(player_id) REFERENCES players(id) ON DELETE CASCADE
        );
    -- # }

    -- # { add
    -- # :name        string
    -- # :x           float
    -- # :y           float
    -- # :z           float
    -- # :world       string
    -- # :yaw         float
    -- # :pitch       float
    -- # :player_name string
    -- # :player_id   int
        INSERT INTO homes(name, x, y, z, yaw, pitch, world, player_name, player_id)
        VALUES(:name, :x, :y, :z, :yaw, :pitch, :world, :player_name, :player_id)
    -- # }

    -- # { delete
    -- # :id int
        DELETE FROM homes
        WHERE id = :id;
    -- # }

    -- # { getAll
        SELECT * FROM homes;
    -- # }

-- # }