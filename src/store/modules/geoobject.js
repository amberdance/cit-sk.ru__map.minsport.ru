import commonMutations from '../commonMutations'
import commonActions from '../commonActions'
import { dispatch } from '../../api'

export default {
  namespaced: true,

  state: {
    items: [],
    placemarks: [],
    categories: [],
    properties: []
  },

  getters: {
    get: (state) => (key) => Object.values(state[key]),
    list: (state) => Object.values(state.items),
    placemarks: (state) => Object.values(state.placemarks),
    categories: (state) => Object.values(state.categories),
    properties: (state) => Object.values(state.properties)
  },

  mutations: {
    ...commonMutations
  },

  actions: {
    ...commonActions,

    async loadData ({ commit }, { route, payload, state = 'items' }) {
      route = `/geoobject/${route}`
      commit('clear', state)

      const responseData = await dispatch.HTTPPost({ route, payload })

      if (route === '/geoobject/get-categories') {
        for (const key in responseData) {
          commit('set', { key: state, props: responseData[key] })
        }

        return
      }

      if (Array.isArray(responseData)) {
        responseData.forEach((item) => {
          commit('set', { key: state, props: item })
        })

        return
      }

      commit('set', { key: state, props: responseData })
    }
  }
}
