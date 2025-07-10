<template>
  <div class="login-container">
    <h1>Login</h1>
    <form @submit.prevent="login">
      <input type="text" v-model="username" placeholder="Username" required />
      <input type="password" v-model="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>
  </div>
</template>

<script src="./Login.script.ts" lang="ts"></script>
<style src="./Login.style.scss" lang="scss"></style>