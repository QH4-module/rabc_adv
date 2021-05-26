DROP TABLE IF EXISTS `tbl_bk_relation_user_depart`;

CREATE TABLE IF NOT EXISTS `tbl_bk_relation_user_depart`
(
    `id`        INT         NOT NULL AUTO_INCREMENT,
    `user_id`   VARCHAR(64) NOT NULL,
    `depart_id` VARCHAR(64) NOT NULL,
    `del_time`  BIGINT      NOT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    COMMENT = 'rabc-用户所属部门';

CREATE INDEX `fk_tbl_bk_relation_user_depart_tbl_bk_user1_idx` ON `tbl_bk_relation_user_depart` (`user_id` ASC);

CREATE INDEX `fk_tbl_bk_relation_user_depart_tbl_bk_depart1_idx` ON `tbl_bk_relation_user_depart` (`depart_id` ASC);
