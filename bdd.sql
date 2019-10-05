CREATE TABLE `games`(`id` INT NOT NULL AUTO_INCREMENT,
                    `game_mode` VARCHAR(10) NOT NULL,
                    `difficult_x` INT DEFAULT 0,
                    `difficult_y` INT DEFAULT 0,
                    `mines` INT DEFAULT 0,
                    `start_time` TIME DEFAULT CURRENT_TIME,
                    `end_time` TIME DEFAULT ADDTIME(CURRENT_TIME, ":10"),
                    PRIMARY KEY (`id`)) ;
                    
INSERT INTO games() VALUES ();