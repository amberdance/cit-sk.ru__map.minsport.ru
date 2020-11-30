import { dispatch } from '../api'

export default {
  async remove ({ commit }, { entity, payload }) {
    await dispatch.HTTPPost({ route: `/${entity}/remove`, payload })

    if (Array.isArray(payload.id)) {
      return payload.id.forEach((id) => {
        commit('remove', { key: 'items', id })
      })
    }

    commit('remove', { key: 'items', id: payload.id })
  },

  async update ({ commit }, { route, payload }) {
    await dispatch.HTTPPost({ route, payload })

    if (Array.isArray(payload.id)) {
      return payload.id.forEach((id) => {
        commit('update', {
          key: 'items',
          props: { id, ...payload }
        })
      })
    }

    commit('update', {
      key: 'items',
      props: { id: payload.id, ...payload }
    })
  },

  async updatePublishState ({ commit }, { payload }) {
    await dispatch.HTTPPost({
      route: '/common/set-state',
      payload: {
        id: payload.id,
        stateId: payload.stateId,
        entity: payload.entity
      }
    })

    if (Array.isArray(payload.id)) {
      return payload.id.forEach((id) => {
        commit('update', {
          key: 'items',
          props: {
            id,
            stateId: payload.stateId,
            stateLabel: payload.stateLabel
          }
        })
      })
    }

    commit('update', {
      key: 'items',
      props: { id: payload.id, ...payload }
    })
  },

  setFilter ({ commit }, payload) {
    commit('setFilter', payload)
  }
}
