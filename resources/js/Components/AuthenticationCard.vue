<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">

        <div class="lg:flex w-full justify-center">
            <div class="lg:w-[620px] w-full h-[670px] sm:max-w-xl mt-7 px-6 py-4 bg-black1 overflow-hidden sm:rounded-l-lg">
                <slot />
            </div>
            <figure class="mt-7 hidden lg:block">
                <img class="h-[670px]" src="@/../../public/images/loginImage.png" alt="logo">
            </figure>
        </div>
    </div>
</template>
