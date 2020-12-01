export default {
  methods: {
    multiRouteInit () {
      const referencePoints = this.route ? this.route.waypoints : []
      const routingMode = this.route ? this.route.routingMode : 'bicycle'

      const multiRoute = new ymaps.multiRouter.MultiRoute(
        {
          referencePoints,
          params: { routingMode }
        },
        {
          boundsAutoApply: true,
          editorMidPointsType: 'via',
          editorDrawOver: false
        }
      )

      this.multiRoute = multiRoute

      if ('entity' in this) this.multirouteEditInit()
      this.yandexMapInstance.geoObjects.add(multiRoute)
    },

    multirouteEditInit () {
      const buttonEditor = new ymaps.control.Button({
        data: { content: 'Режим редактирования' },
        options: { maxWidth: 300 }
      })

      const purgeRoute = new ymaps.control.Button({
        data: { content: 'очистить' },
        options: { visible: false }
      })

      buttonEditor.events.add('select', () => {
        this.multiRoute.editor.start({
          addWayPoints: true,
          removeWayPoints: true
        })
      })

      buttonEditor.events.add('deselect', () => {
        this.multiRoute.editor.stop()
        this.isMultiRouteChanged = true
      })

      purgeRoute.events.add('click', () => {
        this.multiRoute.model.setReferencePoints([])
        purgeRoute.options.set('visible', false)
      })

      this.multiRoute.model.events.add('requestsuccess', () => {
        const activeRoute = this.multiRoute.getActiveRoute()

        if (!activeRoute) return

        this.$refs.entityProperties.setDistance(activeRoute.properties.get(
          'distance'
        ).text)

        this.$refs.entityProperties.setDuration(activeRoute.properties.get(
          'duration'
        ).text)
      })

      this.yandexMapInstance.controls.add(buttonEditor)
    },

    onMultiRouteModeChange () {
      this.multiRoute.model.setParams(
        { routingMode: this.route.routingMode },
        true
      )
    },

    getRouteDuration (dataType = 'text') {
      const activeRoute = this.multiRoute.getActiveRoute()

      return activeRoute.properties.get('duration')[dataType]
    },

    getRouteDistance (dataType = 'text') {
      const activeRoute = this.multiRoute.getActiveRoute()

      return activeRoute.properties.get('distance')[dataType]
    }
  }
}
