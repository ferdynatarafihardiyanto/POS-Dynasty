import React, { useState, useEffect, useRef } from 'react';
import { useCart } from '../context/CartContext';
import { ArrowLeft, Star, Clock, Flame, Sparkles, Check, Plus, Minus, AlertCircle } from 'lucide-react';

export default function MenuDetailModal({ item, onClose }) {
    const { addToCart, tableInfo } = useCart();

    const [quantity, setQuantity] = useState(1);
    const [notes, setNotes] = useState('');
    const [validationError, setValidationError] = useState('');
    const [isNotesFocused, setIsNotesFocused] = useState(false);

    // Refs for smooth auto-scrolling when typing notes
    const scrollContainerRef = useRef(null);
    const notesSectionRef = useRef(null);
    const notesInputRef = useRef(null);

    // Dynamic database modifier groups state
    const [selectedModifiers, setSelectedModifiers] = useState(() => {
        const init = {};
        if (item?.modifier_groups && item.modifier_groups.length > 0) {
            item.modifier_groups.forEach(g => {
                init[g.id] = [];
            });
        }
        return init;
    });

    // Customization states with smart defaults (for mock fallback)
    const [selectedCarb, setSelectedCarb] = useState(() => {
        if (!item?.options_carbs?.choices) return null;
        return item.options_carbs.choices.find(c => c.is_default) || item.options_carbs.choices[0];
    });

    const [selectedSpice, setSelectedSpice] = useState(() => {
        if (!item?.options_spice?.choices) return null;
        return item.options_spice.choices.find(c => c.is_default) || item.options_spice.choices[0];
    });

    const [selectedToppings, setSelectedToppings] = useState([]);

    const scrollToNotes = () => {
        if (notesSectionRef.current && scrollContainerRef.current) {
            const container = scrollContainerRef.current;
            const elemRect = notesSectionRef.current.getBoundingClientRect();
            const containerRect = container.getBoundingClientRect();
            
            // Calculate relative offset within the container with 10px top margin
            const targetTop = elemRect.top - containerRect.top + container.scrollTop - 10;
            
            container.scrollTo({
                top: Math.max(0, targetTop),
                behavior: 'smooth'
            });

            // Secondary scroll for mobile viewports / iOS
            try {
                notesSectionRef.current.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (err) {}
        }
    };

    const handleNotesFocus = () => {
        setIsNotesFocused(true);
        // Multi-stage trigger to ensure smooth scroll during and after keyboard transition
        setTimeout(scrollToNotes, 50);
        setTimeout(scrollToNotes, 250);
        setTimeout(scrollToNotes, 450);
    };

    const handleNotesBlur = () => {
        setTimeout(() => {
            setIsNotesFocused(false);
        }, 150);
    };

    useEffect(() => {
        // Lock body scroll when modal is open
        document.body.style.overflow = 'hidden';
        return () => {
            document.body.style.overflow = 'auto';
        };
    }, []);

    if (!item) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const toggleModifierOption = (group, opt) => {
        setValidationError('');
        setSelectedModifiers(prev => {
            const isSingle = group.tipe === 'single' || group.max_pilihan === 1;
            const currentList = prev[group.id] || [];
            const isSelected = currentList.some(o => o.id === opt.id);

            if (isSingle) {
                if (isSelected) {
                    if (group.wajib_diisi) return prev;
                    return { ...prev, [group.id]: [] };
                }
                return { ...prev, [group.id]: [opt] };
            } else {
                if (isSelected) {
                    return { ...prev, [group.id]: currentList.filter(o => o.id !== opt.id) };
                } else {
                    if (group.max_pilihan && currentList.length >= group.max_pilihan) {
                        setValidationError(`Maksimal ${group.max_pilihan} pilihan untuk ${group.nama}`);
                        return prev;
                    }
                    return { ...prev, [group.id]: [...currentList, opt] };
                }
            }
        });
    };

    const toggleTopping = (topping) => {
        setSelectedToppings(prev => {
            const exists = prev.some(t => t.id === topping.id);
            if (exists) {
                return prev.filter(t => t.id !== topping.id);
            } else {
                return [...prev, topping];
            }
        });
    };

    // Calculate current dynamic item price
    const basePrice = item.harga || 0;
    const carbPrice = selectedCarb?.price || 0;
    const spicePrice = selectedSpice?.price || 0;
    const toppingsPrice = selectedToppings.reduce((sum, t) => sum + (t.price || 0), 0);
    const flatModifiers = Object.values(selectedModifiers).flat();
    const modifiersPrice = flatModifiers.reduce((sum, m) => sum + (parseFloat(m.harga_tambahan) || 0), 0);
    const unitPrice = basePrice + carbPrice + spicePrice + toppingsPrice + modifiersPrice;
    const totalPrice = unitPrice * quantity;

    const handleAddToCart = () => {
        // Validation for modifier groups
        if (item.modifier_groups && item.modifier_groups.length > 0) {
            for (const group of item.modifier_groups) {
                const selected = selectedModifiers[group.id] || [];
                if (group.wajib_diisi && selected.length === 0) {
                    setValidationError(`Varian / Topping "${group.nama}" wajib dipilih!`);
                    return;
                }
                if (group.min_pilihan > 0 && selected.length < group.min_pilihan) {
                    setValidationError(`Pilih minimal ${group.min_pilihan} pilihan untuk "${group.nama}"!`);
                    return;
                }
            }
        }

        const customizations = {
            carb: selectedCarb,
            spice: selectedSpice,
            toppings: selectedToppings,
            modifiers: flatModifiers
        };
        addToCart(item, customizations, quantity, notes);
        onClose();
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md h-[92vh] sm:h-[88vh] bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative">
                
                {/* Fixed Top Nav */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <button
                        onClick={onClose}
                        className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95 cursor-pointer"
                        title="Kembali"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </button>

                    <div className="text-center">
                        <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                            Detail Menu
                        </h2>
                        <p className="text-[11px] font-medium text-[#881B1E]">
                            Kedai Dynasty • Meja {tableInfo.number}
                        </p>
                    </div>

                    {/* Spacer to keep title centered */}
                    <div className="w-9 h-9" aria-hidden="true" />
                </div>

                {/* Scrollable Content Body */}
                <div 
                    ref={scrollContainerRef}
                    className={`flex-1 overflow-y-auto no-scrollbar relative transition-all duration-300 ${
                        isNotesFocused ? 'pb-[380px]' : 'pb-32'
                    }`}
                >
                    {/* Hero Image */}
                    <div className="relative h-64 w-full bg-stone-200 overflow-hidden">
                        <img
                            src={item.gambar}
                            alt={item.nama}
                            className="w-full h-full object-cover"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        {/* Floating Badges */}
                        <div className="absolute top-3 left-3 flex flex-wrap gap-1.5">
                            {item.diskon && (
                                <span className="bg-red-600 text-white font-extrabold text-xs px-2.5 py-1 rounded-full shadow-md">
                                    🏷️ {item.diskon}
                                </span>
                            )}
                            {item.badge && (
                                <span className="bg-amber-400 text-stone-950 font-black text-xs px-2.5 py-1 rounded-full shadow-md flex items-center gap-1">
                                    <Sparkles className="w-3.5 h-3.5" />
                                    {item.badge}
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Main Information Section */}
                    <div className="p-4 bg-white border-b border-stone-200/70">
                        <h1 className="font-display font-extrabold text-xl text-stone-900 tracking-tight">
                            {item.nama}
                        </h1>

                        {/* Price Row */}
                        <div className="mt-2 flex items-baseline gap-2 flex-wrap">
                            <span className="font-display font-black text-2xl text-[#881B1E]">
                                {formatRupiah(item.harga)}
                            </span>
                            {item.harga_coret && (
                                <span className="text-xs text-stone-400 line-through">
                                    {formatRupiah(item.harga_coret)}
                                </span>
                            )}
                            {item.harga_coret && (
                                <span className="text-[11px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-md border border-red-200">
                                    Hemat {formatRupiah(item.harga_coret - item.harga)}
                                </span>
                            )}
                        </div>

                        {/* Meta Tags Asli dari POS */}
                        {item.kategori_nama && (
                            <div className="mt-3 flex items-center gap-2 flex-wrap text-xs font-semibold">
                                <span className="px-2.5 py-1 bg-stone-100 text-stone-700 rounded-xl border border-stone-200/60 text-[11px] font-bold">
                                    {item.kategori_nama}
                                </span>
                            </div>
                        )}

                        {/* Description Asli dari POS */}
                        {item.deskripsi && (
                            <p className="mt-3 text-xs text-stone-600 leading-relaxed">
                                {item.deskripsi}
                            </p>
                        )}
                    </div>

                    {/* Dynamic Database Modifier Groups & Toppings */}
                    {item.modifier_groups && item.modifier_groups.length > 0 && item.modifier_groups.map((group) => {
                        const isSingle = group.tipe === 'single' || group.max_pilihan === 1;
                        const currentSelected = selectedModifiers[group.id] || [];
                        const options = group.options || [];

                        if (options.length === 0) return null;

                        return (
                            <div key={group.id} className="mt-2.5 p-4 bg-white border-y border-stone-200/70">
                                <div className="flex items-center justify-between mb-1">
                                    <h3 className="font-display font-bold text-sm text-stone-900 flex items-center gap-1.5">
                                        <span className="w-1.5 h-1.5 rounded-full bg-[#881B1E]"></span>
                                        {group.nama}
                                    </h3>
                                    <div className="flex items-center gap-1">
                                        {group.wajib_diisi ? (
                                            <span className="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full border border-red-200">
                                                WAJIB
                                            </span>
                                        ) : (
                                            <span className="text-[10px] font-medium text-stone-500 bg-stone-100 px-2 py-0.5 rounded-full">
                                                OPSIONAL
                                            </span>
                                        )}
                                        {group.max_pilihan > 1 && (
                                            <span className="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                                MAKS {group.max_pilihan}
                                            </span>
                                        )}
                                        {isSingle && (
                                            <span className="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                                PILIH 1
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <p className="text-[11px] text-stone-400 mb-3">
                                    {isSingle
                                        ? 'Pilih salah satu varian'
                                        : (group.min_pilihan > 0
                                            ? `Pilih minimal ${group.min_pilihan} hingga ${group.max_pilihan || 'beberapa'} pilihan`
                                            : `Bisa pilih hingga ${group.max_pilihan || 'beberapa'} pilihan`
                                          )
                                    }
                                </p>

                                <div className="space-y-2.5">
                                    {options.map((opt) => {
                                        const isSelected = currentSelected.some(o => o.id === opt.id);
                                        const optPrice = parseFloat(opt.harga_tambahan) || 0;

                                        return (
                                            <div
                                                key={opt.id}
                                                onClick={() => toggleModifierOption(group, opt)}
                                                className={`flex items-center justify-between p-3 rounded-2xl border cursor-pointer transition-all ${
                                                    isSelected
                                                        ? 'border-amber-500 bg-amber-50/50 shadow-xs'
                                                        : 'border-stone-200 hover:border-stone-300 bg-white'
                                                }`}
                                            >
                                                <div className="flex items-center gap-3">
                                                    {isSingle ? (
                                                        <div className={`w-4 h-4 rounded-full border flex items-center justify-center transition-all ${
                                                            isSelected ? 'border-amber-600 bg-amber-500' : 'border-stone-300 bg-white'
                                                        }`}>
                                                            {isSelected && <div className="w-1.5 h-1.5 rounded-full bg-white" />}
                                                        </div>
                                                    ) : (
                                                        <div className={`w-4 h-4 rounded-md border flex items-center justify-center transition-colors ${
                                                            isSelected ? 'border-amber-600 bg-amber-500 text-stone-950' : 'border-stone-300 bg-white'
                                                        }`}>
                                                            {isSelected && <Check className="w-3 h-3 stroke-[3]" />}
                                                        </div>
                                                    )}
                                                    <div>
                                                        <div className="text-xs font-bold text-stone-900">
                                                            {opt.nama}
                                                        </div>
                                                    </div>
                                                </div>
                                                <span className={`text-xs font-bold ${isSelected ? 'text-[#881B1E]' : 'text-stone-700'}`}>
                                                    {optPrice > 0 ? `+${formatRupiah(optPrice)}` : '+Rp 0'}
                                                </span>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        );
                    })}

                    {/* Option 1: Pilihan Nasi / Karbohidrat (Radio) */}
                    {item.options_carbs && (
                        <div className="mt-2.5 p-4 bg-white border-y border-stone-200/70">
                            <div className="flex items-center justify-between mb-1">
                                <h3 className="font-display font-bold text-sm text-stone-900 flex items-center gap-1.5">
                                    <span className="w-1.5 h-1.5 rounded-full bg-[#881B1E]"></span>
                                    {item.options_carbs.title}
                                </h3>
                                {item.options_carbs.required && (
                                    <span className="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full border border-red-200">
                                        WAJIB
                                    </span>
                                )}
                            </div>
                            <p className="text-[11px] text-stone-400 mb-3">
                                {item.options_carbs.subtitle}
                            </p>

                            <div className="space-y-2.5">
                                {item.options_carbs.choices.map((carb) => {
                                    const isSelected = selectedCarb?.id === carb.id;
                                    return (
                                        <label
                                            key={carb.id}
                                            onClick={() => setSelectedCarb(carb)}
                                            className={`flex items-center justify-between p-3 rounded-2xl border cursor-pointer transition-all ${
                                                isSelected
                                                    ? 'border-amber-500 bg-amber-50/40 shadow-xs'
                                                    : 'border-stone-200 hover:border-stone-300 bg-white'
                                            }`}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                                                    isSelected ? 'border-amber-600 bg-amber-500' : 'border-stone-300 bg-white'
                                                }`}>
                                                    {isSelected && <div className="w-1.5 h-1.5 rounded-full bg-white" />}
                                                </div>
                                                <div>
                                                    <div className="text-xs font-bold text-stone-900">
                                                        {carb.name}
                                                    </div>
                                                    <div className="text-[10px] text-stone-400">
                                                        {carb.desc}
                                                    </div>
                                                </div>
                                            </div>
                                            <span className="text-xs font-bold text-stone-700">
                                                {carb.price > 0 ? `+${formatRupiah(carb.price)}` : '+Rp 0'}
                                            </span>
                                        </label>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {/* Option 2: Tingkat Kepedasan Sambal (Radio) */}
                    {item.options_spice && (
                        <div className="mt-2.5 p-4 bg-white border-y border-stone-200/70">
                            <div className="flex items-center justify-between mb-1">
                                <h3 className="font-display font-bold text-sm text-stone-900 flex items-center gap-1.5">
                                    <span className="w-1.5 h-1.5 rounded-full bg-[#881B1E]"></span>
                                    {item.options_spice.title}
                                </h3>
                                <span className="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                    PILIH 1
                                </span>
                            </div>
                            <p className="text-[11px] text-stone-400 mb-3">
                                {item.options_spice.subtitle}
                            </p>

                            <div className="space-y-2.5">
                                {item.options_spice.choices.map((spice) => {
                                    const isSelected = selectedSpice?.id === spice.id;
                                    return (
                                        <label
                                            key={spice.id}
                                            onClick={() => setSelectedSpice(spice)}
                                            className={`flex items-center justify-between p-3 rounded-2xl border cursor-pointer transition-all ${
                                                isSelected
                                                    ? 'border-amber-500 bg-amber-50/40 shadow-xs'
                                                    : 'border-stone-200 hover:border-stone-300 bg-white'
                                            }`}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                                                    isSelected ? 'border-amber-600 bg-amber-500' : 'border-stone-300 bg-white'
                                                }`}>
                                                    {isSelected && <div className="w-1.5 h-1.5 rounded-full bg-white" />}
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <span className="text-xs font-bold text-stone-900">{spice.name}</span>
                                                        {spice.tag && (
                                                            <span className={`text-[9px] px-1.5 py-0.5 rounded-md ${spice.tagColor || 'bg-stone-100 text-stone-600'}`}>
                                                                {spice.tag}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div className="text-[10px] text-stone-400">
                                                        {spice.desc}
                                                    </div>
                                                </div>
                                            </div>
                                            <span className="text-xs font-bold text-stone-700">
                                                {spice.price > 0 ? `+${formatRupiah(spice.price)}` : '+Rp 0'}
                                            </span>
                                        </label>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {/* Option 3: Tambahan Topping / Ekstra (Checkbox Multi-pilih) */}
                    {item.options_toppings && (
                        <div className="mt-2.5 p-4 bg-white border-y border-stone-200/70">
                            <div className="flex items-center justify-between mb-1">
                                <h3 className="font-display font-bold text-sm text-stone-900 flex items-center gap-1.5">
                                    <span className="w-1.5 h-1.5 rounded-full bg-[#881B1E]"></span>
                                    {item.options_toppings.title}
                                </h3>
                                <span className="text-[10px] font-bold text-stone-600 bg-stone-100 px-2 py-0.5 rounded-full">
                                    BISA MULTI-PILIH
                                </span>
                            </div>
                            <p className="text-[11px] text-stone-400 mb-3">
                                {item.options_toppings.subtitle}
                            </p>

                            <div className="space-y-2.5">
                                {item.options_toppings.choices.map((topping) => {
                                    const isChecked = selectedToppings.some(t => t.id === topping.id);
                                    return (
                                        <div
                                            key={topping.id}
                                            onClick={() => toggleTopping(topping)}
                                            className={`flex items-center justify-between p-3 rounded-2xl border cursor-pointer transition-all ${
                                                isChecked
                                                    ? 'border-amber-500 bg-amber-50/40 shadow-xs'
                                                    : 'border-stone-200 hover:border-stone-300 bg-white'
                                            }`}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className={`w-4 h-4 rounded-md border flex items-center justify-center transition-colors ${
                                                    isChecked ? 'border-amber-600 bg-amber-500 text-stone-950' : 'border-stone-300 bg-white'
                                                }`}>
                                                    {isChecked && <Check className="w-3 h-3 stroke-[3]" />}
                                                </div>
                                                <div>
                                                    <div className="text-xs font-bold text-stone-900">
                                                        {topping.name}
                                                    </div>
                                                    <div className="text-[10px] text-stone-400">
                                                        {topping.desc}
                                                    </div>
                                                </div>
                                            </div>
                                            <span className="text-xs font-bold text-amber-700">
                                                +{formatRupiah(topping.price)}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {/* Option 4: Catatan Khusus untuk Koki */}
                    <div 
                        ref={notesSectionRef}
                        className={`mt-2.5 p-4 bg-white border-y border-stone-200/70 transition-all duration-300 ${
                            isNotesFocused ? 'ring-2 ring-[#881B1E]/30 bg-red-50/15' : ''
                        }`}
                    >
                        <div className="flex items-center justify-between mb-1">
                            <h3 className="font-display font-bold text-sm text-stone-900 flex items-center gap-1.5">
                                <span className="w-1.5 h-1.5 rounded-full bg-[#881B1E]"></span>
                                Catatan Khusus untuk Koki
                            </h3>
                            {isNotesFocused ? (
                                <button
                                    type="button"
                                    onMouseDown={(e) => e.preventDefault()}
                                    onClick={() => {
                                        if (notesInputRef.current) notesInputRef.current.blur();
                                        setIsNotesFocused(false);
                                    }}
                                    className="text-[11px] font-bold text-[#881B1E] bg-red-50 hover:bg-red-100 border border-red-200/80 px-2.5 py-0.5 rounded-full transition-all flex items-center gap-1 shadow-xs cursor-pointer active:scale-95"
                                >
                                    <Check className="w-3 h-3 stroke-[2.5]" />
                                    Selesai
                                </button>
                            ) : (
                                <span className="text-[10px] font-medium text-stone-400">
                                    Opsional
                                </span>
                            )}
                        </div>
                        <p className="text-[11px] text-stone-400 mb-2">
                            Beri instruksi khusus untuk persiapan hidangan ini
                        </p>

                        <div className="relative">
                            <textarea
                                ref={notesInputRef}
                                value={notes}
                                onChange={(e) => setNotes(e.target.value.slice(0, 120))}
                                onFocus={handleNotesFocus}
                                onClick={handleNotesFocus}
                                onBlur={handleNotesBlur}
                                rows="2"
                                placeholder="Contoh: Kuah dipisah, jangan pakai daun bawang, sambal banyakin..."
                                className="w-full p-3 rounded-2xl border border-stone-200 bg-stone-50 text-xs text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[#881B1E]/30 focus:border-[#881B1E] focus:bg-white transition"
                            />
                            <div className="text-right text-[10px] text-stone-400 mt-1">
                                {notes.length}/120 karakter
                            </div>
                        </div>
                    </div>
                </div>

                {/* Sticky Bottom Action Bar */}
                <div className="absolute bottom-0 inset-x-0 z-20 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-2xl flex flex-col gap-2.5">
                    {validationError && (
                        <div className="p-2.5 bg-red-50 border border-red-200 rounded-xl text-xs font-bold text-red-700 flex items-center gap-2 animate-fade-in shadow-xs">
                            <AlertCircle className="w-4 h-4 shrink-0 text-red-600" />
                            <span>{validationError}</span>
                        </div>
                    )}
                    <div className="flex items-center gap-3">
                        {/* Quantity Selector */}
                    <div className="flex items-center border border-stone-200 rounded-2xl bg-stone-50 p-1">
                        <button
                            onClick={() => setQuantity(q => Math.max(1, q - 1))}
                            disabled={quantity <= 1}
                            className="w-8 h-8 rounded-xl bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 disabled:opacity-40 disabled:hover:bg-white transition active:scale-95"
                        >
                            <Minus className="w-3.5 h-3.5" />
                        </button>
                        <span className="w-8 text-center font-display font-extrabold text-sm text-stone-900">
                            {quantity}
                        </span>
                        <button
                            onClick={() => setQuantity(q => q + 1)}
                            className="w-8 h-8 rounded-xl bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 transition active:scale-95"
                        >
                            <Plus className="w-3.5 h-3.5" />
                        </button>
                    </div>

                    {/* Add to Cart Submit Button */}
                    <button
                        onClick={handleAddToCart}
                        className="flex-1 py-3 px-4 rounded-2xl bg-gradient-to-r from-[#82181A] via-[#8E1B1E] to-[#6E1214] hover:from-[#751417] hover:to-[#82181A] text-white font-display font-extrabold text-xs tracking-wide shadow-lg shadow-red-950/20 active:scale-[0.98] transition-all flex items-center justify-between"
                    >
                        <span>+ Tambahkan ke Pesanan</span>
                        <span className="bg-white/20 px-2 py-0.5 rounded-lg text-amber-200 text-xs">
                            {formatRupiah(totalPrice)}
                        </span>
                    </button>
                    </div>
                </div>

            </div>
        </div>
    );
}
