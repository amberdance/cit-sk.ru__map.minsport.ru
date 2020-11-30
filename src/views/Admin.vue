<template>
  <MainLayout>
    <el-row type="flex">
      <el-col :span="3">
        <AdminMenu :class="$style.adminMenuWrapper" />
      </el-col>

      <el-col :class="$style.adminContentWrapper">
        <div v-if="$route.path == '/admin'" :class="$style.emptyContent">
          выберите пункт меню
        </div>

        <router-view />
      </el-col>
    </el-row>
  </MainLayout>
</template>

<script>
import AdminMenu from '@/components/admin/common/AdminMenu'
import MainLayout from '@/components/layouts/MainLayout'

export default {
  components: {
    MainLayout,
    AdminMenu
  }
}
</script>
<style module>
.adminMenuWrapper {
  min-height: 95vh;
  padding: 1rem 0 0 0;
  font-size: 14px;
  box-shadow: 3px 4px 3px #dadada;
}
.adminContentWrapper {
  margin-top: 0.1rem;
  margin-left: 0.2rem;
  color: black;
}
.emptyContent {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  margin-top: 20%;
  color: #8a8a8a;
  font-size: 13px;
}
</style>
