<div class="w-full h-full min-h-full flex flex-col">
    <x-page-title label="Chat interna" class="mt-1" />

    <div class="w-full flex-1 flex rounded-lg">
        <div
            class="relative  w-full h-full ps-6 pt-6  pb-4 pe-2  rounded-s-lg bg-white md:w-[360px] lg:w-[400px] xl:w-[500px] shrink-0 overflow-y-auto  ">
            <livewire:wirechat.chats />
        </div>
        <main
            class="hidden md:grid h-full min-h-full w-full  rounded-e-lg bg-white p-4 ps-2 pe-6 pt-6 relative overflow-y-auto"
            style="contain:content">

            <div
                class="m-auto text-center justify-center flex gap-3 h-full w-full rounded-lg flex-col items-center border col-span-12">

                <h4 class=" p-2 px-3 rounded-full font-semibold text-sm bg-gray-custom-1">
                    @lang('wirechat::pages.chat.messages.welcome')</h4>

            </div>
        </main>
    </div>
</div>
