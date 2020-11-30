export default {
  data() {
    return {
      currentPage: 1,
      pageSizes: [10, 50, 100, 200, 300, 400, 500],
      pageSize: 50,
    };
  },

  methods: {
    paginate(rows) {
      let from = this.currentPage * this.pageSize - this.pageSize;
      let to = this.currentPage * this.pageSize;

      return rows.slice(from, to);
    },

    handleCurrentChange(selectedPage) {
      this.currentPage = selectedPage;
    },

    handleSizeChange(size) {
      this.pageSize = size;
    },
  },
};
