import InputError from '@/Components/InputError';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

export default function FormField({ label, error, type = 'text', options = [], ...props }) {
    const id = props.id ?? props.name;
    const control = type === 'textarea'
        ? <Textarea id={id} {...props} />
        : type === 'select'
            ? (
                <Select id={id} {...props}>
                    <option value="">Pilih...</option>
                    {options.map((option) => (
                        <option key={option.value ?? option.id} value={option.value ?? option.id}>
                            {option.label ?? option.nama_template ?? option.nama_customer ?? option.name}
                        </option>
                    ))}
                </Select>
            )
            : <Input id={id} type={type} {...props} />;

    return (
        <div>
            <Label htmlFor={id}>{label}</Label>
            <div className="mt-1">{control}</div>
            <InputError message={error} className="mt-2" />
        </div>
    );
}
