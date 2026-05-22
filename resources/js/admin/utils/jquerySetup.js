import $ from 'jquery'
import DataTable from 'datatables.net-bs5'

DataTable.use($)

window.$ = window.jQuery = $

export { $, DataTable }
