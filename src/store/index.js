import Vue from 'vue'
import Vuex from 'vuex'
import common from './modules/common'
import geoobject from './modules/geoobject'
import hall from './modules/hall'
import route from './modules/route'
import district from './modules/district'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    common,
    district,
    geoobject,
    hall,
    route
  }
})
