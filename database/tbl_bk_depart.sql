DROP TABLE IF EXISTS `tbl_bk_depart`;

CREATE TABLE IF NOT EXISTS `tbl_bk_depart`
(
    `id`          VARCHAR(64)  NOT NULL,
    `name`        VARCHAR(100) NOT NULL COMMENT '部门名称',
    `create_by`   VARCHAR(64)  NOT NULL COMMENT '创建人',
    `create_time` BIGINT       NOT NULL COMMENT '创建时间',
    `desc`        VARCHAR(200) NULL COMMENT '说明',
    `sort`        INT          NOT NULL COMMENT '排序,数字越大越靠前',
    `del_time`    BIGINT       NOT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    COMMENT = 'rabc-部门表';