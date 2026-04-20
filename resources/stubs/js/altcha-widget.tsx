import { useEffect, useMemo } from "react";

type Props = {
    challengeUrl: string;
    auto?: "onload" | "onsubmit" | "onfocus" | "off";
    floating?: "auto" | "top" | "bottom";
    hideFooter?: boolean;
    name?: string;
};

export function AltchaWidget({
    challengeUrl,
    auto = "onsubmit",
    floating = "bottom",
    hideFooter = true,
    name = "altcha",
}: Props) {
    useEffect(() => {
        import("altcha");
    }, []);

    const configuration = useMemo(
        () => JSON.stringify({ floatingPlacement: floating, hideFooter }),
        [floating, hideFooter],
    );

    return (
        <altcha-widget
            auto={auto}
            challenge={challengeUrl}
            configuration={configuration}
            display="floating"
            name={name}
        />
    );
}
