# 网站导航后台管理系统

这是一个基于PHP+MySQL的网站导航后台管理系统，用于管理网站导航页面的数据。系统提供了完整的后台管理功能，包括网站和分类的增删改查，以及RESTful API接口供前端调用。

## 功能特点

- 用户认证系统，安全的登录和会话管理
- 网站管理（添加、编辑、删除、排序）
- 分类管理（添加、编辑、删除、排序）
- RESTful API接口，为前端提供数据
- 响应式界面设计，支持移动端和桌面端
- 图标URL支持，美化导航界面

## 系统架构

- 后端：PHP 原生开发
- 数据库：MySQL
- 前端框架：Bootstrap 5
- 图标库：Bootstrap Icons

## 安装说明

### 系统要求

- PHP 7.0+
- MySQL 5.6+
- Web服务器（Apache/Nginx）

### 安装步骤

1. 将项目文件上传到您的Web服务器
2. 创建MySQL数据库
3. 导入`/db/database.sql`文件到您的数据库
4. 修改`config.php`文件中的数据库连接信息：
   ```php
   // 数据库配置
   define('DB_HOST', 'localhost'); // 数据库主机
   define('DB_USER', 'root');      // 数据库用户名
   define('DB_PASS', '');          // 数据库密码
   define('DB_NAME', 'navpage');   // 数据库名称
   ```
5. 访问网站，使用默认账户登录：
   - 用户名：admin
   - 密码：password

## 使用说明

### 后台管理

1. 登录系统后，您将进入仪表盘页面
2. 通过左侧菜单可以访问不同的功能模块：
   - 仪表盘：显示系统概览和使用统计
   - 网站管理：管理导航网站（添加、编辑、删除、排序）
   - 分类管理：管理网站分类（添加、编辑、删除、排序）
   - API接口：查看API使用说明

### 网站管理

在网站管理页面，您可以：

- 添加新网站：填写网站名称、URL、分类、图标URL和显示顺序
- 编辑网站：修改已有网站的信息
- 删除网站：从系统中移除网站
- 排序：通过设置显示顺序数值调整网站在导航页中的排列顺序（数字越小排序越靠前）

### 分类管理

在分类管理页面，您可以：

- 添加新分类：填写分类名称、标识符和显示顺序
- 编辑分类：修改已有分类的信息
- 删除分类：从系统中移除分类（注意：删除分类会导致该分类下的网站无法正常显示）
- 排序：通过设置显示顺序数值调整分类在导航页中的排列顺序（数字越小排序越靠前）

## API接口

系统提供以下RESTful API接口，可以被前端应用调用：

1. 获取所有分类：
   ```
   GET /api.php?action=get_categories
   ```
   返回示例：
   ```json
   {
     "code": 1,
     "message": "success",
     "data": [
       {"id": 1, "name": "常用", "identifier": "common"},
       {"id": 2, "name": "技术", "identifier": "tech"}
     ]
   }
   ```

2. 获取网站列表（可按分类筛选）：
   ```
   GET /api.php?action=get_websites
   GET /api.php?action=get_websites&category=tech
   ```
   返回示例：
   ```json
   {
     "code": 1,
     "message": "success",
     "data": [
       {
         "id": 1,
         "name": "Google",
         "url": "https://www.google.com",
         "img_url": "https://www.google.com/favicon.ico",
         "category": "common"
       }
     ]
   }
   ```

3. 获取所有数据（分类和网站）：
   ```
   GET /api.php?action=get_all_data
   ```
   返回示例：
   ```json
   {
     "code": 1,
     "message": "success",
     "data": {
       "categories": [...],
       "websites": [...]
     }
   }
   ```

## 前端Vue组件集成

要将此后台系统与Vue前端组件集成，请按照以下步骤修改您的Vue组件：

1. 修改`NavPage.vue`文件，使用API获取数据：

```javascript
// 在script部分添加以下代码
import { ref, computed, onMounted } from 'vue';

// API URL（根据您的实际部署情况修改）
const API_URL = 'http://localhost/navpage-admin/api.php';

// 网站数据和分类数据
const websites = ref([]);
const categories = ref([]);
const activeCategory = ref('all');

// 获取所有数据
async function fetchData() {
  try {
    const response = await fetch(`${API_URL}?action=get_all_data`);
    if (!response.ok) {
      throw new Error('API请求失败');
    }
    const data = await response.json();
    if (data.code === 1 && data.data) {
      websites.value = data.data.websites;
      
      // 转换分类数据格式
      categories.value = [
        { id: 'all', name: '全部' },
        ...data.data.categories.filter(cat => cat.identifier !== 'all')
          .map(cat => ({ id: cat.identifier, name: cat.name }))
      ];
    }
  } catch (error) {
    console.error('获取数据失败:', error);
  }
}

// 根据当前分类过滤网站
const filteredWebsites = computed(() => {
  if (activeCategory.value === 'all') {
    return websites.value;
  }
  return websites.value.filter(site => site.category === activeCategory.value);
});

// 切换分类方法
const changeCategory = (category) => {
  activeCategory.value = category;
};

onMounted(() => {
  fetchData();
});
```

2. 在模板部分添加以下代码：

```html
<template>
  <div class="nav-page">
    <!-- 分类导航 -->
    <div class="categories">
      <div 
        v-for="category in categories" 
        :key="category.id"
        :class="['category-item', { active: activeCategory === category.id }]"
        @click="changeCategory(category.id)"
      >
        {{ category.name }}
      </div>
    </div>
    
    <!-- 网站列表 -->
    <div class="websites-container">
      <div class="websites-grid">
        <a 
          v-for="website in filteredWebsites" 
          :key="website.id"
          :href="website.url"
          target="_blank"
          class="website-card"
        >
          <div class="website-icon">
            <img :src="website.img_url" :alt="website.name">
          </div>
          <div class="website-name">{{ website.name }}</div>
        </a>
      </div>
    </div>
  </div>
</template>
```

3. 确保您的前端项目可以访问后台API（处理跨域问题）

## 安全注意事项

- 在生产环境中，请务必修改默认管理员密码
- 考虑使用HTTPS保护API通信
- 根据需要实现更严格的API访问控制
- 定期备份数据库，防止数据丢失
- 确保服务器环境安全，定期更新PHP和MySQL版本
- 实现API请求限流，防止恶意请求

## 常见问题解答

1. **Q: 如何修改管理员密码？**  
   A: 目前需要直接修改数据库中的用户表记录。未来版本将添加用户管理界面。

2. **Q: 如何添加更多用户？**  
   A: 目前系统仅支持单一管理员账户。多用户支持将在未来版本中添加。

3. **Q: 网站图标无法显示怎么办？**  
   A: 确保图标URL可以公开访问，且格式正确。建议使用https链接以避免混合内容警告。

## 未来计划

- 用户管理功能，支持多用户和权限控制
- 网站访问统计功能
- 批量导入/导出功能
- 更多自定义选项（主题、布局等）
- 移动应用支持

## 许可证

本项目采用MIT许可证。

## 联系与支持

如有问题或建议，请提交Issue或联系开发者。