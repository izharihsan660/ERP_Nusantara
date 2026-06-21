import InputLabel from './InputLabel';
import InputError from '../InputError';

export default function FormRow({ label, error, required = false, optional = false, conditionalNote = '', children }) {
    return (
        <div>
            <InputLabel
                label={label}
                required={required}
                optional={optional}
                conditionalNote={conditionalNote}
            />
            {children}
            {error && <InputError message={error} />}
        </div>
    );
}
