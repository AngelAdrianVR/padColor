<template>
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-100">

        <div class="hidden lg:block w-[75%] h-[calc(100vh-5rem)] rounded-xl bg-[url('@/../../public/images/loginImage.jpg')] bg-cover bg-center bg-no-repeat">
            <div class="lg:w-1/4 w-full h-5/6 bg-white opacity-90 rounded-xl absolute top-14 left-72">
            </div>
            <div class="lg:w-1/4 w-full h-5/6 bg-transparent rounded-xl absolute top-14 left-72">
                <slot />
            </div>
        </div>
        <div class="lg:hidden rounded-tr-[100px] bg-grayED py-5">
            <div class="w-3/4 mx-auto">
                <slot />
            </div>
        </div>
    </div>
</template>
