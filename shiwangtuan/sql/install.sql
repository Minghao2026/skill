-- ============================================================
-- 视旺团官网 数据库初始化脚本 (install.sql)
-- 适用: MySQL 5.7.44 / 8.0+  InnoDB  utf8mb4
-- 规范: 主键 BIGINT UNSIGNED AUTO_INCREMENT; 时间字段 DATETIME;
--       created_at / updated_at / deleted_at(软删除); 索引前缀 <= 191
-- 说明: 本脚本创建全部内容表并写入示例数据(可在后台CMS自由修改)。
--       数据库 swtsite2026 由服务器(宝塔)创建, 此处使用已存在库。
-- ============================================================
USE `swtsite2026`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------- 1. 站点设置 (key/value) -------------------
DROP TABLE IF EXISTS `swt_settings`;
CREATE TABLE `swt_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(32) NOT NULL DEFAULT 'base',
  `sort` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`),
  KEY `idx_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='站点设置';

-- ------------------- 2. 导航菜单 -------------------
DROP TABLE IF EXISTS `swt_nav`;
CREATE TABLE `swt_nav` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(64) NOT NULL,
  `url` VARCHAR(191) NOT NULL DEFAULT '#',
  `target` VARCHAR(16) NOT NULL DEFAULT '_self',
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='导航菜单';

-- ------------------- 3. 首屏轮播 (Banner) -------------------
DROP TABLE IF EXISTS `swt_banner`;
CREATE TABLE `swt_banner` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `image` VARCHAR(255) NULL,
  `button_text` VARCHAR(32) NULL,
  `button_url` VARCHAR(191) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='首屏轮播';

-- ------------------- 4. 板块标题/简介 (section) -------------------
DROP TABLE IF EXISTS `swt_section`;
CREATE TABLE `swt_section` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` VARCHAR(64) NOT NULL,
  `title` VARCHAR(128) NULL,
  `subtitle` VARCHAR(255) NULL,
  `content` TEXT NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_section_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='板块标题与简介';

-- ------------------- 5. 核心优势 (advantage) -------------------
DROP TABLE IF EXISTS `swt_advantage`;
CREATE TABLE `swt_advantage` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(64) NULL,
  `title` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='核心优势';

-- ------------------- 6. 一站式服务 (service) -------------------
DROP TABLE IF EXISTS `swt_service`;
CREATE TABLE `swt_service` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(64) NULL,
  `title` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) NULL,
  `points` TEXT NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='一站式服务模块';

-- ------------------- 7. 行业覆盖 (industry) -------------------
DROP TABLE IF EXISTS `swt_industry`;
CREATE TABLE `swt_industry` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(64) NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(255) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='行业覆盖';

-- ------------------- 8. 业绩数据 (stat) -------------------
DROP TABLE IF EXISTS `swt_stat`;
CREATE TABLE `swt_stat` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(64) NOT NULL,
  `value` VARCHAR(32) NOT NULL,
  `unit` VARCHAR(16) NULL,
  `icon` VARCHAR(64) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='业绩数据指标';

-- ------------------- 9. 客户案例 (case) -------------------
DROP TABLE IF EXISTS `swt_case`;
CREATE TABLE `swt_case` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL,
  `category` VARCHAR(64) NULL,
  `cover` VARCHAR(255) NULL,
  `summary` VARCHAR(512) NULL,
  `metrics` TEXT NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='客户案例';

-- ------------------- 10. AI能力 (ai) -------------------
DROP TABLE IF EXISTS `swt_ai`;
CREATE TABLE `swt_ai` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(64) NULL,
  `title` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='AI能力';

-- ------------------- 11. 服务流程 (process) -------------------
DROP TABLE IF EXISTS `swt_process`;
CREATE TABLE `swt_process` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `step` INT NOT NULL DEFAULT 1,
  `icon` VARCHAR(64) NULL,
  `title` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='服务流程';

-- ------------------- 12. 套餐方案 (plan) -------------------
DROP TABLE IF EXISTS `swt_plan`;
CREATE TABLE `swt_plan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `price` VARCHAR(32) NOT NULL,
  `original_price` VARCHAR(32) NULL,
  `unit` VARCHAR(16) NULL,
  `features` TEXT NULL,
  `is_hot` TINYINT NOT NULL DEFAULT 0,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='套餐方案';

-- ------------------- 13. 合作伙伴/友情链接 (partner) -------------------
DROP TABLE IF EXISTS `swt_partner`;
CREATE TABLE `swt_partner` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `url` VARCHAR(191) NULL,
  `logo` VARCHAR(255) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='合作伙伴';

-- ------------------- 14. 团队/关于 (team) -------------------
DROP TABLE IF EXISTS `swt_team`;
CREATE TABLE `swt_team` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `title` VARCHAR(64) NULL,
  `avatar` VARCHAR(255) NULL,
  `intro` VARCHAR(512) NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_show` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_show` (`is_show`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='团队成员';

-- ------------------- 15. 咨询留言 (message) -------------------
DROP TABLE IF EXISTS `swt_message`;
CREATE TABLE `swt_message` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `phone` VARCHAR(32) NOT NULL,
  `company` VARCHAR(128) NULL,
  `city` VARCHAR(64) NULL,
  `content` VARCHAR(1024) NULL,
  `source` VARCHAR(64) NULL,
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '0未处理 1已联系 2已成交',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_phone` (`phone`(16))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='咨询留言';

-- ------------------- 16. 后台管理员 (admin) -------------------
DROP TABLE IF EXISTS `swt_admin`;
CREATE TABLE `swt_admin` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nickname` VARCHAR(64) NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='后台管理员';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 示例数据 (可在后台CMS自由修改, 业绩数据为示例展示值)
-- ============================================================

INSERT INTO `swt_settings` (`setting_key`, `setting_value`, `setting_group`, `sort`) VALUES
('site_name', '视旺团', 'base', 1),
('site_slogan', '视频号POI团购 · 一站式代运营陪跑', 'base', 2),
('site_description', '视旺团专注视频号POI团购，提供从注册、搭建到运营陪跑的一站式专业化服务，结合AI技术帮助实体商家降本增效、低成本精准拓客。', 'base', 3),
('site_keywords', '视频号POI团购,视频号团购入驻,本地生活团购,商家代运营,AI探店,精准拓客', 'base', 4),
('contact_phone', '400-823-6688', 'contact', 1),
('contact_wechat', 'shiwangtuan2026', 'contact', 2),
('contact_email', 'service@shiwangtuan.com', 'contact', 3),
('contact_address', '中国 · 深圳', 'contact', 4),
('wechat_qr', '', 'contact', 5),
('consultant_qr', '', 'contact', 6),
('icp', '粤ICP备2026000000号-1', 'base', 5),
('copyright', '© 2026 视旺团 版权所有', 'base', 6),
('stats_note', '以上为视旺团合作商家的阶段性经营成果统计，实际效果因行业与门店运营情况而异。', 'base', 7),
('hero_tagline', '微信本地生活 · 万亿流量新蓝海', 'home', 1),
('site_logo', '', 'brand', 8),
('favicon', '', 'brand', 9);

INSERT INTO `swt_nav` (`title`, `url`, `target`, `sort`, `is_show`) VALUES
('首页', '#home', '_self', 1, 1),
('核心优势', '#advantages', '_self', 2, 1),
('一站式服务', '#services', '_self', 3, 1),
('业绩案例', '#performance', '_self', 4, 1),
('AI能力', '#ai', '_self', 5, 1),
('服务流程', '#process', '_self', 6, 1),
('套餐方案', '#plans', '_self', 7, 1),
('关于我们', '#about', '_self', 8, 1),
('联系我们', '#contact', '_self', 9, 1);

INSERT INTO `swt_banner` (`title`, `subtitle`, `button_text`, `button_url`, `sort`, `is_show`) VALUES
('抢占微信本地生活流量红利', '视频号POI团购 · 注册搭建运营陪跑一站式服务', '免费咨询入驻', '#contact', 1, 1),
('AI赋能 实体商家降本增效', '低成本精准拓客 · 私域沉淀复购 · 一人即可运营', '了解我们的服务', '#services', 2, 1);

INSERT INTO `swt_section` (`section_key`, `title`, `subtitle`, `content`, `sort`) VALUES
('what', '什么是视频号POI团购', '视频号本地生活的成交入口', '视频号POI团购并非像传统平台那样直接提交资料就能开卖。官方开放的是POI团购连接通道，成交页需由服务商开发配置——视旺团为您搭建专属团购小程序，用于客户下单与留资。用户从视频/直播里点击门店团购位置链接进入小程序完成购买，到店后凭核销码由商家端扫码核销，利润真正回到老板手里。', 1),
('why', '为什么选择视旺团', '区别于传统平台，真正为商家降本增效', '我们不止是技术服务商，更是陪你把生意做起来的合伙人。从0到1全流程交付，结合AI让获客更准、成本更低、运营更轻。', 2),
('services', '我们的一站式服务', '注册 · 搭建 · 运营陪跑 全链路交付', '覆盖视频号POI团购从入驻到持续运营的每个环节，让商家省心落地、快速见效。', 3),
('industry', '全行业均可入驻', '不限类目、不限门店类型', '本地生活商家均可开通视频号POI团购，把门店搬到同城用户的手机里。', 4),
('performance', '视旺团业绩展示', '用真实结果说话', '我们以结果交付为导向，以下为视旺团服务商家沉淀的阶段性经营成果。', 5),
('ai', 'AI能力 · 让运营更轻', 'AI驱动的内容与获客引擎', '将AI深度融入本地生活经营，探店视频、文案、拓客、复盘全链路智能化，显著降低人力与获客成本。', 6),
('process', '标准化服务流程', '6步快速开通，专人对接', '视旺团全程协助注册、备案、POI挂载与商品对接，最快7-9个工作日即可上线开卖。', 7),
('plans', '套餐方案', '按需选择，灵活合作', '从基础搭建到全案代运营，提供阶梯式合作方案，总有一款适合你的门店。', 8),
('about', '关于视旺团', '专注视频号本地生活的服务商', '视旺团是一支深耕视频号本地生活与AI应用的团队，致力于用技术和服务帮助实体商家实现数字化增长。', 9);

INSERT INTO `swt_advantage` (`icon`, `title`, `description`, `sort`, `is_show`) VALUES
('rocket', '平台红利期 · 自然流量大', '微信视频号POI团购通道红利窗口已开，月活超10亿级，早入驻早占流量位。', 1, 1),
('money', '不抽佣 · 不扣点', '无平台抽佣扣点，也无需保证金，提现仅微信支付0.6%官方手续费。', 2, 1),
('zero', '0粉即可挂载', '无粉丝门槛，新号也能挂门店团购位置；多号矩阵放大同城曝光。', 3, 1),
('ai', 'AI智能降本增效', '结合AI生成探店视频/文案/口播，一人即可运营，获客成本大幅下降。', 4, 1),
('private', '私域沉淀 · 复购裂变', '客户沉淀到商家自有微信，反复触达、复购裂变，永久免费。', 5, 1),
('handshake', '一对一运营陪跑', '从注册、搭建到运营全流程专属顾问陪跑，省心落地快速见效。', 6, 1);

INSERT INTO `swt_service` (`icon`, `title`, `description`, `points`, `sort`, `is_show`) VALUES
('doc', '注册入驻', '营业执照、法人认证、小程序注册与备案全流程代办，资料清单一次性给到位。', "营业执照照片\n法人身份证正反面\n法人手机号\n未注册过公众号的邮箱\n门店门头照、店内照\n对公账户/法人银行卡", 1, 1),
('build', '搭建配置', 'POI门店认领、团购小程序搭建、商品上架与核销链路配置，开箱即用。', "门店POI认领开通\n团购小程序搭建\n商品挂载与上架\n核销码与商家端配置", 2, 1),
('run', '运营陪跑', '专属顾问1对1陪跑，内容策划、直播指导、数据复盘，把方法论交给你。', "内容脚本策划\n直播带货指导\n数据复盘优化\n活动与转化方案", 3, 1),
('smart', 'AI工具赋能', 'AI探店视频、智能文案、精准拓客工具接入，让中小商家也用得起AI。', "AI探店视频生成\n智能文案批量产出\n同城精准拓客\n经营数据智能分析", 4, 1);

INSERT INTO `swt_industry` (`icon`, `name`, `description`, `sort`, `is_show`) VALUES
('food', '餐饮美食', '快餐、正餐、火锅、小吃全品类', 1, 1),
('shop', '购物零售', '商超、便利店、服饰百货', 2, 1),
('beauty', '美业健身', '美容、美发、健身、养生', 3, 1),
('fun', '休闲娱乐', 'KTV、桌游、影院、洗浴', 4, 1),
('life', '生活服务', '维修、家政、洗护、汽车', 5, 1),
('travel', '文旅亲子', '景区、乐园、亲子、研学', 6, 1),
('hotel', '酒店民宿', '酒店、民宿、公寓', 7, 1),
('tea', '茶饮咖啡', '奶茶、咖啡、烘焙新茶饮', 8, 1);

INSERT INTO `swt_stat` (`label`, `value`, `unit`, `icon`, `sort`, `is_show`) VALUES
('累计服务商家', '1200', '+ 家', 'shop', 1, 1),
('累计撮合GMV', '8600', '万+ 元', 'money', 2, 1),
('平均核销率', '92', '%', 'check', 3, 1),
('合作覆盖城市', '36', '城', 'map', 4, 1),
('客户续约率', '96', '%', 'heart', 5, 1),
('视频号POI挂载', '5000', '+ 个', 'pin', 6, 1);

INSERT INTO `swt_case` (`title`, `category`, `summary`, `metrics`, `sort`, `is_show`) VALUES
('某连锁餐饮品牌', '餐饮', '3个月POI团购GMV破280万，新客占比显著提升，门店客流稳步增长。', "GMV:280万+\n新客占比:65%\n核销率:91%", 1, 1),
('某美容养生馆', '美业', '单店月增客300+，客单价提升40%，私域沉淀会员持续复购。', "月增客:300+\n客单价:+40%\n核销率:94%", 2, 1),
('某亲子乐园', '文旅', '直播带货首月破50万，社群复购率38%，节假日爆满。', "首月GMV:50万+\n社群复购:38%\n直播场次:12", 3, 1),
('某茶饮新店', '茶饮', '7天POI上线，首月核销4200单，开业即引爆同城流量。', "上线:7天\n首月核销:4200单\n好评率:98%", 4, 1);

INSERT INTO `swt_ai` (`icon`, `title`, `description`, `sort`, `is_show`) VALUES
('video', 'AI智能探店', '自动生成探店视频、口播文案与分镜，一键发布，低成本持续获客。', 1, 1),
('pen', 'AI文案生成', '标题、卖点、朋友圈、短视频文案批量产出，告别内容创作焦虑。', 2, 1),
('target', 'AI精准拓客', '基于同城人群画像定向触达，把团购链接推给周边最有可能下单的人。', 3, 1),
('chart', 'AI数据复盘', '自动分析转化漏斗与内容表现，给出可执行的运营优化建议。', 4, 1);

INSERT INTO `swt_process` (`step`, `icon`, `title`, `description`, `sort`, `is_show`) VALUES
(1, 'chat', '需求咨询', '免费评估门店与行业，给出专属方案与预期。', 1, 1),
(2, 'group', '付款建群', '专属服务群，专人对接，进度透明可查。', 2, 1),
(3, 'doc', '注册搭建', '注册、认证、备案与团购小程序搭建。', 3, 1),
(4, 'pin', '开通POI', '协助认领开通门店POI地址，挂载团购入口。', 4, 1),
(5, 'box', '商品对接', '挂载团购链接与商品上架，配置核销。', 5, 1),
(6, 'run', '运营陪跑', '内容+直播+数据全流程陪跑，持续迭代。', 6, 1);

INSERT INTO `swt_plan` (`name`, `price`, `original_price`, `unit`, `features`, `is_hot`, `sort`, `is_show`) VALUES
('基础版', '2980', '3980', '元/年', "视频号POI团购注册入驻\n团购小程序搭建\nPOI门店开通\n基础运营指导手册\n工单答疑支持", 0, 1, 1),
('标准版', '5980', '7980', '元/年', "含基础版全部服务\nAI探店/文案工具\n1对1运营陪跑3个月\n内容脚本策划\n数据月度复盘", 1, 2, 1),
('旗舰版', '12800', '16800', '元/年', "含标准版全部服务\n全案代运营托管\nAI矩阵多账号\n专属顾问全年陪跑\n定制增长方案", 0, 3, 1);

INSERT INTO `swt_partner` (`name`, `url`, `sort`, `is_show`) VALUES
('微信视频号', 'https://channels.weixin.qq.com/', 1, 1),
('腾讯智慧零售', 'https://github.com/Tencent/', 2, 1),
('企业微信', 'https://work.weixin.qq.com/', 3, 1),
('微信支付', 'https://pay.weixin.qq.com/', 4, 1);

INSERT INTO `swt_team` (`name`, `title`, `intro`, `sort`, `is_show`) VALUES
('视旺团 · 运营中心', '本地生活运营', '深耕视频号本地生活，精通POI团购从入驻到转化的全链路打法。', 1, 1),
('视旺团 · AI技术', 'AI应用研发', '专注AI内容生成与精准拓客工具，让中小商家低成本用上AI。', 2, 1),
('视旺团 · 客户成功', '陪跑顾问', '一对一陪伴商家落地运营，把方法论与结果一并交付。', 3, 1);

-- 默认管理员: 账号 admin / 密码 admin888 (首次安装后请尽快修改)
INSERT INTO `swt_admin` (`username`, `password`, `nickname`, `role`) VALUES
('admin', '$2b$10$0sM1buW3tg90V5hkNMmOG.wU6HHK2Lu932w5KP0XmQ1Y8GmNKjxO2', '超级管理员', 'admin');
