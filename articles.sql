-- 创建文章分类表
CREATE TABLE IF NOT EXISTS `article_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '分类名称',
  `identifier` varchar(50) NOT NULL COMMENT '分类标识符',
  `description` text COMMENT '分类描述',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级分类ID',
  `display_order` int(11) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章分类表';

-- 创建文章表
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '文章标题',
  `content` longtext NOT NULL COMMENT '文章内容',
  `thumbnail` varchar(255) DEFAULT NULL COMMENT '缩略图',
  `author` varchar(100) DEFAULT NULL COMMENT '作者',
  `category_id` int(11) NOT NULL COMMENT '分类ID',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft' COMMENT '状态：草稿、已发布、已归档',
  `featured` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `article_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

-- 创建文章标签表
CREATE TABLE IF NOT EXISTS `article_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '标签名称',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章标签表';

-- 创建文章标签关联表
CREATE TABLE IF NOT EXISTS `article_tag_relations` (
  `article_id` int(11) NOT NULL COMMENT '文章ID',
  `tag_id` int(11) NOT NULL COMMENT '标签ID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`article_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `article_tag_relations_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `article_tag_relations_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `article_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章标签关联表';

-- 插入默认文章分类数据
INSERT INTO `article_categories` (`name`, `identifier`, `description`, `display_order`) VALUES
('新闻', 'news', '新闻资讯', 1),
('教程', 'tutorials', '使用教程', 2),
('公告', 'announcements', '系统公告', 3),
('其他', 'others', '其他内容', 4);