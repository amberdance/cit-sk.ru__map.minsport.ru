<template>
  <div :class="$style.root">
    <!-- <div class="form-item">
      <div class="filter-label">Возраст:</div>
      <el-slider
        v-model="age"
        :step="1"
        :min="1"
        :max="100"
        class="filter-slider"
      >
      </el-slider>
    </div> -->

    <div class="form-item">
      <div class="filter-label">Вес:</div>
      <el-slider
        v-model="weight"
        :step="1"
        :min="1"
        :max="200"
        class="filter-slider"
      >
      </el-slider>
    </div>

    <div class="form-item">
      <div class="filter-label">Рост:</div>
      <el-slider
        v-model="height"
        :step="1"
        :min="1"
        :max="220"
        class="filter-slider"
      >
      </el-slider>
    </div>

    <div v-if="routeMode == 'pedestrian'" class="form-item">
      <div class="filter-label">Интенсивность ходьбы:</div>
      <el-select v-model="coef" class="filter-item">
        <el-option label="средний темп" :value="4"></el-option>
        <el-option label="быстрый темп" :value="6"></el-option>
        <el-option label="бег" :value="8"></el-option>
      </el-select>
    </div>

    <div v-else class="form-item">
      <div class="filter-label">Интенсивность езды:</div>
      <el-select v-model="coef" class="filter-item">
        <el-option label="средний темп" :value="10"></el-option>
        <el-option label="быстрый темп" :value="15"></el-option>
        <el-option label="ускоренный темп" :value="20"></el-option>
      </el-select>
    </div>

    <div class="form-item">
      <div class="filter-label">Результат, кКал:</div>

      <animated-number
        :value="pedestrianCalories"
        :formatValue="formatValue"
        :duration="200"
      ></animated-number>
    </div>
  </div>
</template>
<script>
import AnimatedNumber from "animated-number-vue";

export default {
  components: {
    AnimatedNumber,
  },

  props: {
    routeProperties: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      distance: 0,
      duration: 0,
      routeMode: "pedestrian",

      age: 1,
      weight: 69,
      height: 180,
      coef: 4,
      gender: true,
    };
  },

  computed: {
    calories() {
      return this.routeMode == "pedestrian"
        ? this.pedestrianCalories
        : this.bicycleCalories;
    },

    /**
     *0,035 * М + (V2/H) * 0,029 * М, где М — вес тела человека, H — рост человека, V — скорость ходьбы
     */
    pedestrianCalories() {
      let result =
        0.35 * this.weight +
        (Math.pow(this.coef, 2) / this.height) * this.weight * this.duration;

      return Number(result);
    },

    bicycleCalories() {
      let result =
        0.35 * this.weight +
        (this.coef / this.height) * this.weight * this.duration;

      return Number(result);
    },
  },

  created() {
    const { duration, distance, routingMode } = this.routeProperties;

    this.duration = duration / 60;
    this.distance = distance;
    this.routeMode = routingMode;

    if (routingMode == "bicycle") this.coef = 10;
  },

  methods: {
    formatValue(value) {
      return Number(value).toFixed(2);
    },
  },
};
</script>
<style module>
.root {
  width: 50%;
  margin: auto;
  margin: 2rem 0;
}
.flexBox {
  display: flex;
  align-items: center;
}
</style>
