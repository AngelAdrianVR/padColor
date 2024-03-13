<template>
    <div v-if="showUsersList" @click="showUsersList = false" class="inset-0 absolute top-0 left-0 z-10"></div>
    <div class="relative w-full">
        <!-- <header
            class="border border-b-0 border-grayD9 bg-[#CCCCCC] rounded-tl-[3px] rounded-tr-[3px] h-7 flex items-center">
           <button type="button" @click="toggleStyle('bold')" :class="{ 'text-primary': styles.bold }"
                class="border-r border-grayD9 px-3 text-sm">
                <i class="fa-solid fa-bold"></i>
            </button>
            <button type="button" @click="toggleStyle('italic')" :class="{ 'text-primary': styles.italic }"
                class="border-r border-grayD9 px-3 text-sm">
                <i class="fa-solid fa-italic"></i>
            </button>
            <button type="button" @click="toggleStyle('underline')" :class="{ 'text-primary': styles.underline }"
                class="border-r border-grayD9 px-3 text-sm">
                <i class="fa-solid fa-underline"></i>
            </button> 
        </header> -->
        <div class="flex border border-grayD9 rounded-t-lg focus:border-primary">
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm rounded-full pl-1 pt-2 w-8">
                <img class="size-7 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name" />
            </div>
            <div contenteditable="true" @input="onInput" ref="editor" id="editor" @keypress="checkForAtSign"
                class="bg-transparent placeholder:text-gray-400 text-gray-700 text-sm block w-full py-3 px-1 min-h-[85px] focus:outline-none"
                :class="{ 'rounded-none': withFooter || hasMedia }">
            </div>
        </div>
        <!-- Make mentions -->
        <footer v-if="withFooter"
            class="border border-t-0 border-grayD9 bg-transparent rounded-br-lg rounded-bl-lg p-2 flex justify-between">
            <button @click="showUsersList = !showUsersList" type="button"
                class="text-primary text-sm cursor-pointer">@Mención</button>
            <PrimaryButton type="button" @click="$emit('submitComment')" :disabled="disabled">Agregar comentarios
            </PrimaryButton>
            <transition name="fade">
                <ul v-if="showUsersList"
                    class="z-20 border border-[#a9a9a9] absolute -bottom-20 left-28 rounded-[3px] bg-white w-60 h-36 overflow-y-auto">
                    <template v-for="item in usersSorted" :key="item.id">
                        <li v-if="item.id !== $page.props.auth.user.id" type="button" @click="mentionUser(item)"
                            class="flex items-center px-2 py-1 space-x-2 text-xs mb-1 hover:bg-primarylight cursor-pointer">
                            <img class="size-8 rounded-full object-cover" :src="item.profile_photo_url"
                                :alt="item.name" />
                            <p>{{ item.name }}</p>
                        </li>
                    </template>
                    <p v-if="!userList.length" class="text-gray-500 text-xs text-center my-8">No hay usuarios para
                        mencionar
                    </p>
                </ul>
            </transition>
        </footer>

        <!-- If has media -->
        <footer v-if="hasMedia"
            class="border border-t-0 border-grayD9 bg-transparent rounded-br-lg rounded-bl-lg p-2 flex justify-between">
            <FileUploader @files-selected="filesChanged" />
            <PrimaryButton class="self-start" type="button" @click="$emit('submitComment')" :disabled="disabled">
                Publicar
            </PrimaryButton>
        </footer>

    </div>
</template>
<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import FileUploader from "@/Components/MyComponents/FileUploader.vue";

export default {
    data() {
        return {
            styles: {
                bold: false,
                italic: false,
                underline: false
            },
            showUsersList: false,
            mentions: [],
            content: null,
            media: null
        };
    },
    components: {
        PrimaryButton,
        CancelButton,
        FileUploader
    },
    props: {
        // Propiedad para recibir y enviar el valor del componente padre
        withFooter: {
            type: Boolean,
            default: false
        },
        hasMedia: {
            type: Boolean,
            default: false
        },
        userList: {
            type: Array,
            default: []
        },
        disabled: {
            type: Boolean,
            default: false
        },
        defaultValue: {
            type: String,
            default: ''
        },
    },
    emits: ['content', 'submitComment'],
    methods: {
        filesChanged(files) {
            this.media = files;
        },
        clearContent() {
            const editor = this.$refs.editor;
            editor.innerHTML = null;
            this.mentions = [];
        },
        toggleStyle(style) {
            const editor = this.$refs.editor;
            editor.focus();
            this.styles[style] = !this.styles[style];
            document.execCommand(style, false, null);
            editor.focus();
        },
        addUserToMentions(user) {
            const userWithSomeProperties = { id: user.id, name: user.name };
            this.mentions.push(userWithSomeProperties);
        },
        mentionUser(user) {
            // Comprobar si el usuario ya ha sido mencionado
            const editor = this.$refs.editor;
            if (!this.mentions.some(mention => mention.id === user.id)) {
                const cursorPosition = editor.selectionEnd;
                const text = editor.innerHTML;
                const mention = `<span id="m-${user.id}" class="text-primary">@${user.name}</span> &nbsp;`;

                // Insertar la mención en el contenido existente
                const newText = text.slice(0, cursorPosition) + mention;

                // Actualizar el contenido del editor usando innerHTML
                editor.innerHTML = newText;

                // Registrar el usuario mencionado en el arreglo
                this.mentions.push({ id: user.id, tag: `@${user.name}` });

                // Enfocar el editor
                editor.focus();
                // Establecer el cursor al final del contenido
                this.setCaretPositionToEnd(editor);

            }
            // Enfocar el editor
            editor.focus();
            // Establecer el cursor al final del contenido
            this.setCaretPositionToEnd(editor);
            // Cerrar la lista de usuarios
            this.showUsersList = false;

            this.$emit('content', this.$refs.editor.innerHTML);
        },
        onInput() {
            const editor = this.$refs.editor;
            const text = editor.innerHTML;
            const mentionElements = editor.querySelectorAll('span[id^="m-"]');

            if (!mentionElements.length && this.mentions.length) this.mentions = [];

            // Iterar sobre las menciones en orden inverso para evitar problemas con los índices al borrar
            for (let i = this.mentions.length - 1; i >= 0; i--) {
                const mention = this.mentions[i];
                const mentionId = `m-${mention.id}`;
                const mentionElement = Array.from(mentionElements).find((element) => element.id === mentionId);

                if (mentionElement && mentionElement.innerText !== mention.tag) {
                    // Eliminar la mención tanto del editor como del arreglo mentions
                    this.mentions.splice(i, 1);
                    editor.removeChild(mentionElement);
                }
            }

            // Actualiza el contenido del editor y emite el evento personalizado "content"
            this.$emit('content', this.$refs.editor.innerHTML);
        },
        checkForAtSign(event) {
            const editor = this.$refs.editor;
            if (event.key === '@') {
                // Mostrar la lista de usuarios cuando se escribe "@".
                this.showUsersList = true;
                // Enfocar el editor nuevamente para continuar escribiendo.
                editor.focus();
                // Evitar que el carácter "@" aparezca en el editor.
                event.preventDefault();
            }
        },
        setCaretPositionToEnd(elem) {
            const range = document.createRange();
            const sel = window.getSelection();
            range.selectNodeContents(elem);
            range.collapse(false);
            sel.removeAllRanges();
            sel.addRange(range);
        },
    },
    computed: {
        usersSorted() {
            return this.userList.sort((a, b) => {
                if (a.name < b.name) {
                    return -1;
                }
                if (a.name > b.name) {
                    return 1;
                }
                return 0;
            });
        }
    },
    mounted() {
        // Establecer el contenido inicial del editor con el valor por defecto y aplicar estilos si es necesario
        if (this.defaultValue) {
            this.$refs.editor.innerHTML = this.defaultValue;
            // this.updateContent();
        }
    },
}
</script>
<style scoped>
.slide-fade-enter-active,
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>