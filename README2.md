# JONAS网站导航系统

一个现代化的网站导航系统，提供"一个主页，整个世界"的便捷访问体验。

## 项目概述

这是一个基于 Vue.js 和 PHP 的全栈网站导航系统，采用前后端分离的架构设计。前端使用 Vite 作为构建工具，后端使用 PHP 提供 RESTful API 服务，数据存储使用 MySQL 数据库。

## 技术栈

### 前端
- Vue.js - 渐进式 JavaScript 框架
- Vite - 现代前端构建工具
- HTML5/CSS3 - 页面结构和样式
- JavaScript ES6+ - 客户端脚本

### 后端
- PHP - 服务端脚本语言
- MySQL - 关系型数据库
- RESTful API - 前后端通信接口

## 项目结构

```
hao123.web025.cn/
├── api.php              # API 接口文件
├── index.html           # 前端入口文件
├── README.md           # 项目说明文件
├── vite.svg            # 项目图标
├── assets/             # 静态资源目录
│   ├── css/           # CSS 样式文件
│   └── js/            # JavaScript 文件
├── db/                 # 数据库相关文件
│   └── database.sql   # 数据库结构文件
├── include/            # PHP 包含文件
│   └── config.php     # 配置文件
└── navpageAdmin/      # 后台管理系统
    ├── api-docs.php   # API 文档
    ├── api-test.php   # API 测试
    ├── categories.php # 分类管理
    ├── dashboard.php  # 控制面板
    ├── index.php      # 后台首页
    ├── logout.php     # 退出登录
    ├── websites.php   # 网站管理
    └── assets/        # 后台静态资源
        ├── css/       # 后台样式文件
        ├── images/    # 后台图片资源
        └── js/        # 后台脚本文件
```

## 主要功能

1. 前台功能
   - 网站分类展示
   - 网站列表浏览
   - 响应式布局设计
   - 跨域支持

2. 后台管理
   - 用户认证系统
   - 分类管理
   - 网站管理
   - API 文档和测试

## 数据库设计

系统包含以下主要数据表：

1. users - 用户表
   - 存储管理员账户信息
   - 包含用户名、密码、邮箱等字段

2. categories - 分类表
   - 存储网站分类信息
   - 包含分类名称、标识符等字段

3. websites - 网站表
   - 存储具体网站信息
   - 关联对应的分类

## API 接口

系统提供 RESTful API，主要包括：

1. GET /api.php?action=get_categories
   - 获取所有网站分类

2. GET /api.php?action=get_websites
   - 获取网站列表
   - 支持按分类筛选

## 安全特性

1. 跨域保护
   - 配置适当的 CORS 头
   - 支持 OPTIONS 预检请求

2. 用户认证
   - 会话管理
   - 登录状态检查

3. 数据库安全
   - 使用 UTF-8MB4 字符集
   - 密码加密存储

## 部署要求

- PHP 7.0+
- MySQL 5.7+
- Web服务器（Apache/Nginx）
- 现代浏览器支持
