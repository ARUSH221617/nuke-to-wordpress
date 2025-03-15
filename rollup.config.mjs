import resolve from "@rollup/plugin-node-resolve";
import commonjs from "@rollup/plugin-commonjs";
import terser from "@rollup/plugin-terser";
import postcss from "rollup-plugin-postcss";

const production = process.env.NODE_ENV === "production";

export default {
  input: "admin/js/src/main.js",
  output: {
    file: "admin/js/dist/admin.min.js",
    format: "iife",
    sourcemap: !production,
  },
  plugins: [
    resolve(),
    commonjs(),
    postcss({
      extract: true,
      minimize: production,
    }),
    production && terser(),
  ],
};
