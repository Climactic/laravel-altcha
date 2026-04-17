declare namespace JSX {
    interface IntrinsicElements {
        "altcha-widget": React.DetailedHTMLProps<
            React.HTMLAttributes<HTMLElement> & {
                auto?: string;
                challengeurl?: string;
                floating?: string;
                hidefooter?: boolean;
                name?: string;
            },
            HTMLElement
        >;
    }
}
