export default {
  data () {
    return {
      multipleSelection: [],
      pageSizes: [10, 50, 100, 200],
      pageSize: 50
    }
  },

  computed: {
    isAdmin () {
      return this.$isAdmin()
    }
  },

  methods: {
    handleSelectionChange (rows) {
      this.multipleSelection = []

      rows.forEach(({ id, stateId }) =>
        this.multipleSelection.push({ id, stateId })
      )
    },

    async removeItem () {
      await this.warningNotice(this.multipleSelection.length)

      try {
        this.$isLoading()

        const removedRows = this.multipleSelection.map((item) => item.id)

        await this.$store.dispatch(`${this.entity}/remove`, {
          entity: this.entity,
          payload: {
            id: removedRows.length === 1 ? removedRows[0] : removedRows
          }
        })

        this.$refs.dataTable.clearSelection()
        this.$onSuccess()
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    async updatePublishState ({ stateId, stateLabel, rows }) {
      try {
        this.$isLoading()

        await this.warningNotice(
          rows.length,
          10,
          'Это может занять некоторое время'
        )

        if (!Array.isArray(rows)) rows = [rows]

        const mappedRows = rows.map((item) => item.id)

        await this.$store.dispatch(`${this.entity}/updatePublishState`, {
          payload: {
            id: mappedRows.length === 1 ? mappedRows[0] : mappedRows,
            stateId,
            stateLabel,
            entity: this.entity
          }
        })

        this.$refs.dataTable.clearSelection()
        this.$onSuccess()
      } catch (e) {
        return
      } finally {
        this.$isLoading(false)
      }
    },

    async warningNotice (
      initialLength,
      permittedLength = 5,
      text = 'Действие необратимо, вы уверены ?'
    ) {
      if (initialLength > permittedLength) {
        try {
          await this.$confirm(text, {
            confirmButtonText: 'да',
            cancelButtonText: 'надо подумать',
            type: 'warning'
          })
        } catch (e) {
          this.$refs.dataTable.clearSelection()
          return Promise.reject('cancelled')
        }
      }
    },

    rowClassNameByState ({ row }) {
      switch (row.stateId) {
        case 1:
          return 'success-row'

        case 2:
          return 'warning-row'

        case 4:
          return 'danger-row'
      }
    }
  }
}
