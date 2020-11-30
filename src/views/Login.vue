<template>
  <div :class="$style.root">
    <div :class="$style.formWrapper">
      <el-form
        ref="form"
        :model="formData"
        :rules="formRules"
        @submit.native.prevent="authorize"
      >
        <span :class="$style.title">Добро пожаловать</span>
        <el-form-item prop="login">
          <el-input
            name="login"
            v-model="formData.login"
            placeholder="логин"
            autofocus
            prefix-icon="el-icon-user"
          />
        </el-form-item>

        <el-form-item prop="password">
          <el-input
            name="password"
            type="password"
            v-model="formData.password"
            placeholder="пароль"
            prefix-icon="el-icon-lock"
            show-password
          />
        </el-form-item>

        <el-button type="primary" native-type="submit" :loading="isLoading"
          >все верно</el-button
        >
      </el-form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isLoading: false,

      formData: {
        login: "",
        password: "",
      },

      formRules: {
        login: [
          {
            required: true,
            message: " ",
          },
        ],

        password: [
          {
            required: true,
            message: " ",
          },
        ],
      },
    };
  },

  methods: {
    async authorize() {
      await this.$refs.form.validate();

      try {
        this.isLoading = true;
        await this.$logIn(this.formData);
        this.$router.push("/admin");
      } catch (e) {
        if (e == "Bad request") return this.$onError();

        if (e.config.response.status == 401)
          return this.$onError("Все - таки что - то неверно");

        if (e.config.response.status == 403)
          return this.$onError("Доступ запрещен");
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style module>
.root {
  display: flex;
  height: 100vh;
  justify-content: center;
  background-size: cover;
  background-attachment: fixed;
  background-position: center;
}
.formWrapper {
  display: flex;
  justify-content: center;
}
.formWrapper form {
  display: flex;
  justify-content: center;
  flex-direction: column;
  background-color: #246c8d99;
  padding: 20px;
}
.formWrapper button {
  background-color: #2bc8ef;
  border: none;
}
.formWrapper button:hover {
  background-color: #65aed2 !important;
}
.formWrapper button:active {
  background-color: #2bc8ef !important;
}
.formWrapper button:focus {
  background-color: #2bc8ef !important;
}
.title {
  text-align: center;
  color: white;
  font-size: 14px;
  padding: 0.5rem;
  margin-bottom: 1rem;
}
</style>
