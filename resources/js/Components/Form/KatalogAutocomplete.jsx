import { Input } from '@/Components/ui/input';
import { formatRupiah } from '@/utils/currency';
import { useEffect, useRef, useState, useCallback } from 'react';
import { computePosition, flip, size, offset } from '@floating-ui/dom';

export default function KatalogAutocomplete({ value, onSelect, placeholder = 'Cari katalog...' }) {
    const [query, setQuery] = useState(value?.part_no ? `${value.part_no} — ${value.deskripsi ?? value.nama_barang ?? ''}` : '');
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(false);
    const [open, setOpen] = useState(false);
    const [activeIndex, setActiveIndex] = useState(0);
    const wrapperRef = useRef(null);
    const inputRef = useRef(null);
    const dropdownRef = useRef(null);

    const updatePosition = useCallback(() => {
        if (!inputRef.current || !dropdownRef.current || !open) return;

        computePosition(inputRef.current, dropdownRef.current, {
            placement: 'bottom-start',
            middleware: [
                offset(4),
                flip(),
                size({
                    apply({ availableWidth, availableHeight, elements }) {
                        const inputWidth = inputRef.current?.offsetWidth || 0;
                        const viewportWidth = window.innerWidth || availableWidth;
                        Object.assign(elements.floating.style, {
                            maxHeight: `${Math.min(300, availableHeight)}px`,
                            width: `${Math.min(Math.max(inputWidth, 320), viewportWidth - 24, availableWidth)}px`,
                            minWidth: `${Math.min(inputWidth || 320, viewportWidth - 24)}px`,
                        });
                    },
                }),
            ],
        }).then(({ x, y }) => {
            Object.assign(dropdownRef.current.style, {
                left: `${x}px`,
                top: `${y}px`,
            });
        });
    }, [open]);

    useEffect(() => {
        if (open) {
            updatePosition();
        }
    }, [open, updatePosition]);

    useEffect(() => {
        const controller = new AbortController();
        setLoading(true);

        fetch(route('katalog.search', { q: query, limit: 10 }), {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        })
            .then((response) => response.json())
            .then((results) => {
                setItems(results);
                setActiveIndex(0);
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    setItems([]);
                }
            })
            .finally(() => setLoading(false));

        return () => controller.abort();
    }, [query]);

    useEffect(() => {
        const close = (event) => {
            if (!wrapperRef.current?.contains(event.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', close);

        return () => document.removeEventListener('mousedown', close);
    }, []);

    const choose = (item) => {
        setQuery(`${item.part_no} — ${item.nama_barang}`);
        setOpen(false);
        onSelect?.(item);
    };

    const onKeyDown = (event) => {
        if (!open && ['ArrowDown', 'ArrowUp', 'Enter'].includes(event.key)) {
            setOpen(true);
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveIndex((index) => Math.min(index + 1, Math.max(items.length - 1, 0)));
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveIndex((index) => Math.max(index - 1, 0));
        }

        if (event.key === 'Enter' && open && items[activeIndex]) {
            event.preventDefault();
            choose(items[activeIndex]);
        }
    };

    return (
        <div ref={wrapperRef} className="relative w-full">
            <Input
                ref={inputRef}
                value={query}
                onChange={(event) => {
                    setQuery(event.target.value);
                    setOpen(true);
                }}
                onFocus={() => setOpen(true)}
                onKeyDown={onKeyDown}
                placeholder={placeholder}
            />
            {open && (
                <div
                    ref={dropdownRef}
                    className="fixed z-50 overflow-y-auto rounded-md border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                    style={{ maxHeight: '300px' }}
                >
                    {loading && <div className="px-3 py-2 text-sm text-slate-500">Memuat...</div>}
                    {!loading && items.length === 0 && (
                        <div className="px-3 py-2 text-sm text-slate-500">Tidak ada hasil</div>
                    )}
                    {!loading &&
                        items.map((item, index) => (
                            <button
                                key={item.id}
                                type="button"
                                className={`block w-full px-3 py-2.5 text-left transition-colors ${index === activeIndex ? 'bg-slate-100 dark:bg-slate-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800'}`}
                                onMouseEnter={() => setActiveIndex(index)}
                                onClick={() => choose(item)}
                            >
                                <div className="font-medium text-slate-900 dark:text-slate-100">
                                    {item.part_no} — {item.nama_barang}
                                </div>
                                <div className="mt-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                    {formatRupiah(item.harga_jual_default)}
                                </div>
                            </button>
                        ))}
                </div>
            )}
        </div>
    );
}
