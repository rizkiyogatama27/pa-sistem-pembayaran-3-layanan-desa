<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-xl border border-transparent bg-gradient-to-r from-sky-700 via-blue-700 to-teal-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition ease-in-out duration-150 hover:from-sky-600 hover:via-blue-600 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
