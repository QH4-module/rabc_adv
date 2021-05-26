DROP TABLE IF EXISTS `tbl_bk_depart_relation`;

CREATE TABLE IF NOT EXISTS `tbl_bk_depart_relation`
(
    `id`        VARCHAR(64) NOT NULL,
    `depart_id` VARCHAR(64) NOT NULL COMMENT '部门id',
    `parent_id` VARCHAR(64) NOT NULL COMMENT '上级id',
    `del_time`  BIGINT      NOT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    COMMENT = 'rabc-部门-层级关系';

CREATE INDEX `fk_tbl_bk_depart_relation_tbl_bk_depart1_idx` ON `tbl_bk_depart_relation` (`depart_id` ASC);

CREATE INDEX `fk_tbl_bk_depart_relation_tbl_bk_depart2_idx` ON `tbl_bk_depart_relation` (`parent_id` ASC);
