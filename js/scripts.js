// highligth code
hljs.highlightAll();

const App = {
	dialogs: document.querySelector("oa-dialogs"),
	toast: document.querySelector("oa-toast"),
	loader: document.querySelector("oa-loader"),
	tryDelete(el) {
		const params = {
			message: `Are you sure you want to delete this item: ${el.dataset.name}`,
			title: 'CAUTION'
		}
		this.dialogs.deploy(params, () => {
			// OK button callback
			this.toast.show({
				message: `Sorry, DB is read-only!`,
				type: 'error'
			})
		}, () => {
			// CANCEL button callback
			this.toast.show({
				message: `Nothing to delete...`,
				type: 'warning'
			})
		});
	},
	tryEdit(el) {
		this.toast.show({
			message: `You can handle this action using this record ID: ${el.dataset.id}`,
			type: 'success'
		})
	},
	tryDetails(el) {
		this.toast.show({
			message: `You can use this record ID: ${el.dataset.id} to route to an edit view or URL`,
			type: 'success'
		})
	},
}