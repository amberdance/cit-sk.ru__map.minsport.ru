<template>
  <header class="header-wrapper shadowed">
    <el-row class="header-content">
      <el-col style="padding:1rem 0;">
        <el-row type="flex">
          <!-- <el-col :lg="2" :sm="5" :xs="10">
            <router-link to="/map"
              ><img :src="avatar" class="avatar"
            /></router-link>
          </el-col> -->

          <el-col :lg="18">
            <div :class="$style.heading">
              Интерактивная карта спортивных сооружений и маршрутов
              Ставропольского края
            </div>
          </el-col>
        </el-row>
      </el-col>

      <el-col :xs="18" :class="$style.rightColumn">
        <div :class="$style.ministryHeading">
          <el-link href="https://minsport.ru" target="_blank">
            Министерство физической культуры и спорта Ставропольского края
          </el-link>
        </div>
      </el-col>

      <el-col v-show="$isAuthorized()" :lg="2" :xs="2">
        <div :class="$style.logoutWrapper">
          <font-awesome-icon icon="sign-out-alt" size="lg" @click="logout" />
        </div>
      </el-col>
    </el-row>
  </header>
</template>
<script>
export default {
  // data() {
  //   return {
  //     avatar: require("@/assets/logo.png"),
  //   };
  // },

  methods: {
    logout () {
      this.$logOut()
      if (this.$route.path !== '/map') this.$router.push('/map')
    }
  }
}
</script>
<style module>
.ministryHeading a {
  color: white !important;
  transition: color 0.5s linear;
  font-size: 17px;
}
.heading {
  font-size: 17px;
  margin: 0.6rem 0;
}
.rightColumn {
  display: flex;
  justify-content: flex-end;
}
.logoutWrapper svg,
.logoutWrapper i {
  cursor: pointer;
  padding: 1.25rem;
  font-size: 22px;
  color: white;
}
.logoutWrapper {
  display: flex;
  align-items: center;
  margin: 0 0.2rem;
}
</style>
