<template>
  <div :class="$style.loader"></div>
</template>

<style module>
.loader {
  position: fixed;
  z-index: 9999;
  top: 0;
  left: 0;
  box-sizing: border-box;
  width: 100%;
  height: 100vh;
  overflow: hidden;
  color: #000000;
  transition: opacity 0.7s ease;
  opacity: 1;
  background-color: #9292928c;
}
.loader:after,
.loader:before {
  box-sizing: border-box;
}
.loader:after {
  position: absolute;
  top: calc(50% - 25px);
  left: calc(50% - 25px);
  width: 50px;
  height: 50px;
  content: "";
  animation: rotation 1s linear infinite;
  border: solid 8px #ffffff;
  border-left-color: transparent;
  border-radius: 50%;
}
.loader:before {
  position: absolute;
  top: 60%;
  left: 50%;
  transform: translateX(-50%);
  height: 48px;
  content: "";
  color: #fff;
  font-size: 24px;
  text-align: center;
}
@keyframes rotation {
  from {
    transform: rotate(0);
  }
  to {
    transform: rotate(359deg);
  }
}
@keyframes blink {
  from {
    opacity: 0.5;
  }
  to {
    opacity: 1;
  }
}
</style>
