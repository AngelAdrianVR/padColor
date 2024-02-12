import axios from "axios";

export default (await import('vue')).defineComponent({
data() {
return {
showNotificationPopup: false,
allItems: false,
notifications: [],
selectedItems: [],
};
},
components: {
Dropdown,
DropdownLink,
PrimaryButton,
Checkbox,
},
methods: {
// toggleNotificationPopup() {
//     this.showNotificationPopup = !this.showNotificationPopup;
// },
handleChange() {
if (this.allItems) {
this.selectedItems = this.notifications.map(notification => notification.id);
} else {
this.selectedItems = [];
}
},
handleItemChecked() {
if (this.selectedItems.length == this.notifications.length) {
this.allItems = true;
} else if (this.selectedItems.length < this.notifications.length && this.allItems) {
this.allItems = false;
}
},
async deleteNotifications() {
try {
const response = await axios.post(route('users.delete-user-notifications'), { notifications_ids: this.selectedItems });

if (response.status === 200) {
// Filtrar el arreglo excluyendo los elementos con IDs en 'selectedItems'
this.notifications = this.notifications.filter(item => !this.selectedItems.includes(item.id));
this.$notify({
title: "Éxito",
message: response.data.message,
type: "success",
});
}
} catch (error) {
console.log(error);
this.$notify({
title: "No se pudo completar la solicitud",
message: "El servidor no pudo procesar la petición, inténtalo más tarde",
type: "error",
});
}
},
async fetchNotifications() {
try {
const response = await axios.get(route('users.get-notifications'));

if (response.status === 200) {
this.notifications = response.data.items;
}
} catch (error) {
console.log(error);
this.$notify({
title: "No se pudo completar la solicitud",
message: "El servidor no pudo procesar la petición para obtener las notificaciones. Inténtalo más tarde",
type: "error",
});
}
},
async readNotifications() {
try {
const response = await axios.post(route("users.read-user-notifications"));

if (response.data.unread) {
this.fetchNotifications();
}
} catch (error) {
console.log(error);
}
},
},
computed: {
getUnreadMessages() {
return this.notifications?.filter(item => item.read_at === null);
}
},
mounted() {
this.fetchNotifications();
},
});
