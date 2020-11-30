<template>
  <div :class="$style.menuWrapper">
    <el-menu router unique-opened>
      <el-menu-item
        v-for="item in rootMenuItems"
        :key="item.index"
        :index="item.route"
      >
        <i :class="item.icon || 'el-icon-menu'"></i>
        <span slot="title">{{ item.title }}</span>
      </el-menu-item>

      <el-submenu
        v-for="item in childrenMenuItems"
        :key="item.index"
        :index="item.index"
      >
        <template slot="title">
          <i class="el-icon-menu"></i>
          <span slot="title">{{ item.title }}</span>
        </template>

        <el-menu-item-group>
          <el-menu-item
            v-for="children in item.childrens"
            :key="children.index"
            :index="children.route"
            >{{ children.label }}</el-menu-item
          >
        </el-menu-item-group>
      </el-submenu>
    </el-menu>
  </div>
</template>

<script>
export default {
  data () {
    return {
      rootMenuItems: [
        {
          title: 'Карта',
          isParent: false,
          index: '4',
          route: '/map',
          icon: 'el-icon-location'
        }
      ],

      childrenMenuItems: [
        {
          title: 'Объекты',
          index: '1',
          isParent: true,

          childrens: [
            {
              label: 'создать',
              route: '/admin/geo-add',
              index: '1-1'
            },
            {
              label: 'список',
              route: '/admin/geo-list',
              index: '1-2'
            }
          ]
        },

        {
          title: 'Залы',
          index: '2',
          isParent: true,

          childrens: [
            {
              label: 'создать',
              route: '/admin/hall-add',
              index: '2-1'
            },
            {
              label: 'список',
              route: '/admin/hall-list',
              index: '2-2'
            }
          ]
        },

        {
          title: 'Маршруты',
          index: '3',
          isParent: true,

          childrens: [
            {
              label: 'создать',
              route: '/admin/route-add',
              index: '3-1'
            },
            {
              label: 'список',
              route: '/admin/route-list',
              index: '3-2'
            }
          ]
        }
      ]
    }
  }
}
</script>
<style module>
.menuWrapper {
  display: flex;
  flex-direction: column;
  min-width: 200px;
}
.menuWrapper a {
  color: black;
}
.menuTitle {
  color: grey;
  text-align: center;
  padding: 1rem;
  border-top: 1px #d2d2d240 solid;
  border-bottom: 1px #d2d2d240 solid;
}

.menuItem a {
  display: flex;
  align-items: center;
  min-height: 35px;
  padding: 0.5rem;
  transition: background 0.2s ease-in-out;
}
.menuItem a:hover {
  background: #35bcc3;
  color: white;
}
</style>
