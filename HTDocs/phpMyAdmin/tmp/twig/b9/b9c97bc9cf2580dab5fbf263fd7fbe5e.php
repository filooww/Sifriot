<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* server/replication/status_table.twig */
class __TwigTemplate_ccfade9dc998210d551a5b5e50f6d7f4 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<div id=\"replication_";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["type"] ?? null), "html", null, true);
        yield "_section\"";
        yield ((($context["is_hidden"] ?? null)) ? (" style=\"display: none;\"") : (""));
        yield ">
  ";
        // line 2
        if (($context["has_title"] ?? null)) {
            // line 3
            yield "    <h4>
      <a id=\"replication_";
            // line 4
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["type"] ?? null), "html", null, true);
            yield "\"></a>
      ";
            // line 5
            if ((($context["type"] ?? null) == "primary")) {
                // line 6
                yield "        ";
yield _gettext("Primary status");
                // line 7
                yield "      ";
            } else {
                // line 8
                yield "        ";
yield _gettext("Replica status");
                // line 9
                yield "      ";
            }
            // line 10
            yield "    </h4>
  ";
        }
        // line 12
        yield "
  <table id=\"server";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["type"] ?? null), "html", null, true);
        yield "replicationsummary\" class=\"table w-auto\">
    <thead>
      <tr>
        <th>";
yield _gettext("Variable");
        // line 16
        yield "</th>
        <th>";
yield _gettext("Value");
        // line 17
        yield "</th>
      </tr>
    </thead>

    <tbody>
      ";
        // line 22
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["variables"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["variable"]) {
            // line 23
            yield "        <tr>
          <td>";
            // line 24
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["variable"], "name", [], "any", false, false, false, 24), "html", null, true);
            yield "</td>
          <td class=\"text-end font-monospace\">
            <span";
            // line 26
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["variable"], "status", [], "any", false, false, false, 26) == "text-danger")) {
                yield " class=\"text-danger\"";
            } elseif ((CoreExtension::getAttribute($this->env, $this->source, $context["variable"], "status", [], "any", false, false, false, 26) == "text-success")) {
                yield " class=\"text-success\"";
            }
            yield ">
              ";
            // line 27
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["variable"], "value", [], "any", false, false, false, 27), "html", null, true);
            yield "
            </span>
          </td>
        </tr>
      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['variable'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 32
        yield "    </tbody>
  </table>
</div>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "server/replication/status_table.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  124 => 32,  113 => 27,  105 => 26,  100 => 24,  97 => 23,  93 => 22,  86 => 17,  82 => 16,  75 => 13,  72 => 12,  68 => 10,  65 => 9,  62 => 8,  59 => 7,  56 => 6,  54 => 5,  50 => 4,  47 => 3,  45 => 2,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "server/replication/status_table.twig", "C:\\MyInfo\\WebProg\\HTDocs\\phpMyAdmin\\templates\\server\\replication\\status_table.twig");
    }
}
