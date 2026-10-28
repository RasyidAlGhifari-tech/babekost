<div class="rounded-lg border border-line-chip bg-white p-3">
    <p class="text-xs font-semibold">Tata Tertib Kost</p>

    <div class="mt-2 max-h-48 overflow-y-auto pr-1">
        <ol class="list-decimal space-y-1.5 pl-4 text-[11px] leading-relaxed text-slate">
            @foreach (config('babekost.house_rules') as $rule)
                <li>{{ $rule }}</li>
            @endforeach
        </ol>
    </div>

    <label class="mt-3 flex items-start gap-2 border-t border-line-chip pt-3 text-xs font-medium">
        <input type="checkbox" name="agree_rules" value="1" required @checked(old('agree_rules'))
                class="mt-0.5 rounded border-line-chip text-gold-700 focus:ring-0">
        Saya telah membaca dan menyetujui tata tertib kost di atas.
    </label>
</div>